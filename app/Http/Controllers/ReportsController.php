<?php

namespace App\Http\Controllers;

use App\DataObjects\Report;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePackage;
use App\Models\SalePackageItem;
use App\Repository\InventoryRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class ReportsController
 *
 * @package App\Http\Controllers
 */
class ReportsController extends AuthenticatedController
{
    protected $inventoryRepo;

    public function __construct(InventoryRepository $inventoryRepo)
    {
        parent::__construct();

        $this->middleware('can:access,'.Report::class);

        $this->inventoryRepo = $inventoryRepo;
    }

    public function sales(Request $request)
    {
        $branch = Branch::find($request->get('branch'));
        $from   = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->timestamp)->startOfDay();
        $to     = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $mode   = $request->get('mode') ?: 'daily';
        $type   = $request->get('type') ?: 'all';

        if (!$branch) {
            $sales = new Collection();
        } else {
            $salesQuery = Sale::where('branch_id', '=', $branch->id)
                ->finished()
                ->paid()
                ->whereBetween('opened_at', [$from, $to]);

            switch ($type) {
                case 'walkin':
                    $salesQuery = $salesQuery->where('is_delivery', '=', false);
                    break;
                case 'delivery':
                    $salesQuery = $salesQuery->where('is_delivery', '=', true);
                    break;
            }

            $sales = $salesQuery->get();
        }

        return view('reports.sales', [
            'branchId' => $request->get('branch'),
            'branches' => Branch::orderBy('name', 'asc')->get(),
            'sales'    => $sales,
            'from'     => $from,
            'to'       => $to,
            'mode'     => $mode,
            'type'     => $type
        ]);
    }

    public function stock(Request $request)
    {
        $branch  = Branch::find($request->get('branch'));
        $product = Product::find($request->get('product'));
        $from    = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->timestamp)->startOfDay();
        $to      = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $mode    = $request->get('mode') ?: 'daily';

        if (!$branch || !$product) {
            $movements = new Collection();
        } else {
            $movements = (new Collection($this->inventoryRepo->getMovements($product, $branch)))->map(function ($movement) {
                return (object) $movement;
            });
        }

        return view('reports.stock', [
            'branchId'  => $request->get('branch'),
            'productId' => $request->get('product'),
            'product'   => $product,
            'branches'  => Branch::orderBy('name', 'asc')->get(),
            'products'  => Product::orderBy('name', 'asc')->get(),
            'movements' => $movements,
            'from'      => $from,
            'to'        => $to,
            'mode'      => $mode
        ]);
    }

    public function product(Request $request)
    {
        $branch    = Branch::find($request->get('branch'));
        $product   = Product::find($request->get('product'));
        $from      = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->timestamp)->startOfDay();
        $to        = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $mode      = $request->get('mode') ?: 'daily';
        $grouped   = [];
        $formatter = [
            'daily'   => [
                'increment' => function (Carbon $date) { return $date->addDays(1); },
                'label'     => function (Carbon $date) { return $date->toFormattedDateString(); },
                'group'     => function (Carbon $date) { return $date->toDateString(); },
                'compare'   => function (Carbon $date1, Carbon $date2) { return $date1->lte($date2); },
            ],
            'weekly'  => [
                'increment' => function (Carbon $date) { return $date->addWeek(1); },
                'label'     => function (Carbon $date) { return $date->startOfWeek()->toFormattedDateString(); },
                'group'     => function (Carbon $date) { return $date->format('W Y'); },
                'compare'   => function (Carbon $date1, Carbon $date2) { return $date1->lte($date2) || $date1->format('YW') === $date2->format('YW'); },
            ],
            'monthly' => [
                'increment' => function (Carbon $date) { return $date->addMonth(1); },
                'label'     => function (Carbon $date) { return $date->format('M Y'); },
                'group'     => function (Carbon $date) { return $date->format('M Y'); },
                'compare'   => function (Carbon $date1, Carbon $date2) { return $date1->lte($date2) || $date1->format('YM') === $date2->format('YM'); },
            ],
        ];

        if (!$branch || !$product) {
            $sales = new Collection();
        } else {
            $productIds = [$product->id];
            $containers = Product::where('product_item_id', '=', $product->id)->get();
            $sales      = Sale::select('sales.*')
                ->leftJoin('sale_items', function (JoinClause $query) use ($product) {
                    return $query->on('sales.id', '=', 'sale_items.sale_id')
                        ->where('sale_items.product_id', '=', $product->id);
                })
                ->leftJoin('sale_packages', 'sales.id', '=', 'sale_packages.sale_id')
                ->leftJoin('sale_package_items', function (JoinClause $query) use ($product) {
                    return $query->on('sale_packages.id', '=', 'sale_package_items.sale_package_id')
                        ->where('sale_package_items.product_id', '=', $product->id);
                })
                ->whereNotNull('sales.paid_at')
                ->where(function ($query) {
                    return $query->whereNotNull('sale_items.id')
                        ->orWhereNotNull('sale_package_items.sale_package_id');
                })
                ->groupBy('sales.id')
                ->get();
        }

        foreach ($sales as $sale) {
            $date = $formatter[$mode]['group']($sale->opened_at);

            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'date'     => $sale->opened_at,
                    'quantity' => 0
                ];
            }

            foreach ($sale->getRefundableItems() as $saleItem) {
                if ($saleItem->product_id == $product->id) {
                    $grouped[$date]['quantity'] += $saleItem->quantity;
                }
            }

            foreach ($sale->getRefundablePackages() as $salePackage) {
                foreach ($salePackage->items as $salePackageItem) {
                    if ($salePackageItem->product_id == $product->id) {
                        $grouped[$date]['quantity'] += ($salePackage->quantity * $salePackageItem->quantity);
                    }
                }
            }
        }

        $chart = [
            'labels'   => [],
            'datasets' => [
                [
                    'label'           => $product->name,
                    'backgroundColor' => '#f87979',
                    'data'            => []
                ]
            ]
        ];

        for ($now = $from->copy(); $formatter[$mode]['compare']($now, $to); $now = $formatter[$mode]['increment']($now)) {
            $group = $formatter[$mode]['group']($now);

            $chart['labels'][]              = $formatter[$mode]['label']($now);
            $chart['datasets'][0]['data'][] = isset($grouped[$group])
                ? $grouped[$group]['quantity']
                : 0;
        }

        return view('reports.product', [
            'branchId'  => $request->get('branch'),
            'productId' => $request->get('product'),
            'product'   => $product,
            'totalSold' => array_sum(array_column($grouped, 'quantity')),
            'chart'     => $chart,
            'branches'  => Branch::orderBy('name', 'asc')->get(),
            'products'  => Product::orderBy('name', 'asc')->get(),
            'movements' => $sales,
            'from'      => $from,
            'to'        => $to,
            'mode'      => $mode
        ]);
    }
}