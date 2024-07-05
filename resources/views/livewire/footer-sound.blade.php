<div x-data>
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
@push("scripts")
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

  })
</script>
@endpush
