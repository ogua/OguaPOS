<?php

namespace App\Filament\Pages;

use App\Models\Warehouse;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make("filter")
                        ->options([
                            'today' => 'Today',
                            'week' => 'Last 7 Days',
                            'lastmonth' => 'Last month',
                            'month' => 'This month',
                            'year' => 'This year',
                        ])
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function($state,$set){
                            if ($state == "today") {
                                $set("startDate",date('Y-m-d'));
                                $set("endDate",date('Y-m-d'));
                            }

                            if ($state == "week") {
                                $set("startDate",date('Y-m-d', strtotime("-7 days")));
                                $set("endDate",date('Y-m-d'));
                            }

                            if ($state == "lastmonth") {
                                $set("startDate",Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'));  
                                $set("endDate",Carbon::now()->endOfMonth()->subMonth()->format('Y-m-d'));
                            }

                            if ($state == "month") {
                                $set("startDate",date("Y").'-'.date("m").'-'.'01');
                                $set("endDate",date("Y").'-'.date("m").'-'.date('t', mktime(0, 0, 0, date("m"), 1, date("Y"))));
                            }

                            if ($state == "year") {
                                $set("startDate",strtotime(date("Y") .'-01-01'));
                                $set("endDate",strtotime(date("Y") .'-12-31'));
                            }

                        }),
                        DatePicker::make('startDate')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                        Select::make('warehouse_id')
                            ->options(Warehouse::pluck('name','id'))
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(4),
            ]);
    }
}