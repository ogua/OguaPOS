<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
 
        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
 
        <title>{{ config('app.name') }}</title>
 
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
 
        @filamentStyles
        @vite('resources/css/app.css')
    </head>
 
    <body>
        {{ $slot }}

        <div>
            <audio ref="myfootersound" id="myfootersound" preload="auto">
            <source src="{{url('/beep/beep-timber.mp3')}}"></source>
            </audio>

            <audio ref="mysoundclip1" id="mysoundclip1" preload="auto">
              <source src="{{url('/beep/beep-07.mp3')}}"></source>
            </audio>

            <audio ref="erroalert" id="erroalert" preload="auto">
              <source src="{{url('/beep/error.mp3')}}"></source>
            </audio>
        </div>

        <iframe id="invoiceFrame" style="display: none;"></iframe>

        @livewire('notifications')
 
        @filamentScripts
        @vite('resources/js/app.js')
    </body>
    <script>
  document.addEventListener('DOMContentLoaded', function(){
    const audio = document.querySelector('[ref="myfootersound"]');

    const playaudio = document.querySelector('[ref="mysoundclip1"]');

    const erroalert = document.querySelector('[ref="erroalert"]');

    Livewire.on('success-sound', () => {
      audio.play();
    })


    Livewire.on('play-sound', () => {
      playaudio.play();
    })

    Livewire.on('play-error', () => {
      erroalert.play();
    })

    Livewire.on('printInvoice', (saleId) => {
      // const printWindow = window.open('/pos-invoice/' + saleId, '_blank');
      // printWindow.print();

      fetch(`/pos-invoice/${saleId}`)
                .then(response => response.text())
                .then(html => {
                    const iframe = document.getElementById('invoiceFrame');
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                    iframe.style.display = 'block';
                    iframe.contentWindow.focus();
                    //iframe.contentWindow.print();
                });
    })

  })
</script>
</html>