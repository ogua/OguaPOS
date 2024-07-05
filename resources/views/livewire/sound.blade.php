<div x-data>
    <audio ref="mysoundclip1" id="mysoundclip1" preload="auto">
      <source src="{{url('/beep/beep-07.mp3')}}"></source>
    </audio>
</div>
@push("scripts")
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const audio = document.querySelector('[ref="mysoundclip1"]');

    Livewire.on('play-sound', () => {
      audio.play();
    })
  })
 // alert("working");
    //var audio = $("#mysoundclip1").play();
</script>
@endpush
