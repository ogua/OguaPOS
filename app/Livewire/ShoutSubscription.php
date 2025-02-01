<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\User;
use Livewire\Component;
use Filament\Forms\Form;
use Awcodes\Shout\Components\Shout;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Awcodes\Shout\Components\ShoutEntry;
use Filament\Forms\Concerns\InteractsWithForms;

class ShoutSubscription extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public $message;
    public ?bool $expiry;

    public function mount(): void
    {
        $cexpiry = auth()->user()->company?->exp_date;

        $ndate = strtotime(now());
        $edate = strtotime($cexpiry);

        $nyear = date('Y', $ndate);
        $eyear = date('Y', $edate);

        $nmonth = date('m', $ndate);
        $emonth = date('m', $edate);

        $nday = date('d', $ndate);
        $eday = date('d', $edate);

        $ndiff = ($eyear - $nyear) * 12 + ($emonth - $nmonth);

        $ddiff = $emonth - $nmonth + ($eday - $nday);

        //$role = auth()->user()->roles[0]->name;

        if ($ndiff == '0') {
            if ($ddiff == '0') {
                $this->message = 'Your subcription expires today ' . date('D d M Y', strtotime($cexpiry));
            } else {
                $this->message = 'Your subcription expires this month on ' . date('D d M Y', strtotime($cexpiry));
            }
            $this->expiry = true;
        } else {
            $this->expiry = false;
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([Shout::make('so-important')
            ->visible($this->expiry)
            ->content($this->message)
            ->type('danger')->columnSpanFull()]);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = User::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.shout-subscription');
    }
}
