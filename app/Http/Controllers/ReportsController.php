<?php

namespace App\Http\Controllers;

use App\DataObjects\Report;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Repository\InventoryRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

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
        $branch      = Branch::find($request->get('branch'));
        $from        = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->addDay(1)->timestamp)->startOfDay();
        $to          = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $mode        = $request->get('mode') ?: 'daily';
        $type        = $request->get('type') ?: 'all';
        $paymentType = $request->get('payment_type') ?: 'all';

        if (!$branch) {
            $sales = new Collection();
        } else {
            $salesQuery = Sale::with([
                    'customer.group',
                    'items',
                    'packages',
                    'refunds',
                    'openedBy',
                    'payments.sale.items',
                    'payments.sale.packages',
                    'payments.sale.refunds',
                    'payments.sale.customer.group'
                ])
                ->finished()
                ->paid()
                ->select('sales.*')
                ->join('sale_payments', 'sales.id', '=', 'sale_payments.sale_id')
                ->where('sales.branch_id', '=', $branch->id)
                ->whereBetween('sales.paid_at', [$from, $to])
                ->orderBy('sales.paid_at', 'desc')
                ->groupBy('sales.id');

            switch ($type) {
                case 'walkin':
                    $salesQuery = $salesQuery->where('sales.is_delivery', '=', false)
                        ->where('sales.is_wholesale', '=', false);
                    break;
                case 'delivery':
                    $salesQuery = $salesQuery->where('sales.is_delivery', '=', true);
                    break;
                case 'wholesale':
                    $salesQuery = $salesQuery->where('sales.is_wholesale', '=', true);
                    break;
            }

            switch ($paymentType) {
                case 'cash':
                    $salesQuery = $salesQuery->where('sale_payments.payment_method', '=', SalePayment::PAYMENT_METHOD_CASH);
                    break;
                case 'credit_card':
                    $salesQuery = $salesQuery->where('sale_payments.payment_method', '=', SalePayment::PAYMENT_METHOD_CREDIT_CARD);
                    break;
            }

            $sales = $salesQuery->get();
        }

        if ($request->get('print')) {
            Excel::create('report-sales-'.$from->format('Ymd').'-'.$to->format('Ymd'), function (LaravelExcelWriter $excel) use ($mode, $sales) {
                $excel->sheet('list', function (LaravelExcelWorksheet $sheet) use ($mode, $sales) {
                    if ($mode === 'daily') {
                        $sheet->fromArray($sales->map(function (Sale $sale) {
                            return [
                                'Date'                 => $sale->opened_at->toDayDateTimeString(),
                                'Type'                 => $sale->getType(),
                                'Receipt SN'           => $sale->getCode(),
                                'Cashier / User'       => $sale->openedBy->name,
                                'Payment'              => $sale->payments->first()->payment_method,
                                'Client'               => $sale->customer->name,
                                'Price'                => money_format('%.2n', $sale->calculateSubTotal()),
                                'Discount'             => $sale->sales_discount
                                    ? number_format($sale->sales_discount, 1).($sale->sales_discount_type === 'PERCENTAGE' ? '%' : ' AED')
                                    : '-',
                                'After Discount Price' => money_format('%.2n', $sale->calculateTotal()),
                                'Paid Amount'          => money_format('%.2n', $sale->payments->first()->amount),
                                'Post Tax'             => money_format('%.2n', $sale->payments->first()->getNetPaid())
                            ];
                        })->toArray());
                    } else {
                        $sheet->fromArray($sales->groupBy(function ($sale) { return $sale->opened_at->toFormattedDateString(); })->map(function ($groupedSales, $date) {
                            return [
                                'Date'  => $date,
                                'Total' => $groupedSales->map(function ($byDateSales) {
                                    return $byDateSales->calculateTotal();
                                })->sum()
                            ];
                        })->toArray());
                    }

                    $sheet->setColumnFormat(['A:Z' => '@']);
                });
            })->download('csv');
        }

        return view('reports.sales', [
            'branchId'    => $request->get('branch'),
            'branches'    => Branch::active()->licensed()->orderBy('name', 'asc')->get(),
            'sales'       => $sales,
            'from'        => $from,
            'to'          => $to,
            'mode'        => $mode,
            'type'        => $type,
            'paymentType' => $paymentType
        ]);
    }

    public function stock(Request $request)
    {
        $branch  = Branch::find($request->get('branch'));
        $product = Product::find($request->get('product'));
        $from    = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->addDay(1)->timestamp)->startOfDay();
        $to      = Carbon::createFromTimestamp($request->get('to') ?: Carbon::now()->timestamp)->endOfDay();
        $mode    = $request->get('mode') ?: 'daily';

        if (!$branch || !$product) {
            $movements = new Collection();
        } else {
            $movements = (new Collection($this->inventoryRepo->getMovements($product, $branch, $from, $to)))->map(function ($movement) {
                return (object) $movement;
            });

            if ($mode === 'weekly') {
                $movementLabels = [];

                for ($start = $from->copy()->startOfWeek(); $start->lte($to); $start->addWeek(1)) {
                    $movementKey                  = 'Week '.$start->format('W').' ('.$start->startOfWeek()->toDateString().')';
                    $movementLabels[$movementKey] = [
                        'from' => $start->copy()->startOfWeek(),
                        'to'   => $start->copy()->endOfWeek(),
                        'in'   => 0,
                        'out'  => 0
                    ];

                    foreach ($movements as $movement) {
                        if ((int) $movement->date->weekOfYear === (int) $start->weekOfYear) {
                            if ($movement->targetBranch && $movement->targetBranch->id == $request->get('branch')) {
                                $movementLabels[$movementKey]['in'] += abs($movement->quantity);
                            } else {
                                $movementLabels[$movementKey]['out'] += abs($movement->quantity);
                            }
                        }
                    }
                }

                $movements = $movementLabels;
            } elseif ($mode === 'monthly') {
                $movementLabels = [];

                for ($start = $from->copy()->startOfMonth(); $start->lte($to); $start->addMonth(1)) {
                    $movementKey                  = $start->format('F Y');
                    $movementLabels[$movementKey] = [
                        'from' => $start->copy()->startOfMonth(),
                        'to'   => $start->copy()->endOfMonth(),
                        'in'   => 0,
                        'out'  => 0
                    ];

                    foreach ($movements as $movement) {
                        if ((int) $movement->date->month === (int) $start->month) {
                            if ($movement->targetBranch && $movement->targetBranch->id == $request->get('branch')) {
                                $movementLabels[$movementKey]['in'] += abs($movement->quantity);
                            } else {
                                $movementLabels[$movementKey]['out'] += abs($movement->quantity);
                            }
                        }
                    }
                }

                $movements = $movementLabels;
            }
        }

        return view('reports.stock', [
            'branchId'  => $request->get('branch'),
            'productId' => $request->get('product'),
            'product'   => $product,
            'branches'  => Branch::active()->licensed()->orderBy('name', 'asc')->get(),
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
        $from      = Carbon::createFromTimestamp($request->get('from') ?: Carbon::now()->subWeek(1)->addDay(1)->timestamp)->startOfDay();
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
            $sales = Sale::with([
                    'customer.group',
                    'items',
                    'packages',
                    'refunds.items',
                    'refunds.packages',
                    'openedBy',
                    'payments.sale.items',
                    'payments.sale.packages',
                    'payments.sale.refunds',
                    'payments.sale.customer.group'
                ])
                ->select('sales.*')
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
                    'label'           => $product ? $product->name : '',
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
            'branches'  => Branch::active()->licensed()->orderBy('name', 'asc')->get(),
            'products'  => Product::orderBy('name', 'asc')->get(),
            'movements' => $sales,
            'from'      => $from,
            'to'        => $to,
            'mode'      => $mode
        ]);
    }
}