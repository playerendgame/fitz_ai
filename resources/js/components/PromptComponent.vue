<template>
    <div>
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6">
                    <!-- <div class="buttons text-center">
                        <button class="btn btn-outline-light" @click="theAudioWave">Speak</button>
                    </div> -->
                    <!-- <canvas id="waveform" width="500" height="600"></canvas> -->
                     <textarea class="form-control" v-model="prompt"></textarea><br>
                    <button class="btn btn-outline-light" @click="predict">Talk</button>
                </div>
                <div class="col-md-6">
                    <div class="border text-light">
                        {{ response }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
import WaveSurfer from 'wavesurfer.js';

export default {
  data(){
    return{
      prompt: '',
      response: ''
    }
  },
  methods: {
    // theAudioWave() {
    //   navigator.mediaDevices.getUserMedia({ audio: true })
    //     .then(stream => {
    //       const audioContext = new AudioContext();
    //       const source = audioContext.createMediaStreamSource(stream);
    //       const analyzer = audioContext.createAnalyser();
    //       source.connect(analyzer);
    //       analyzer.fftSize = 256;

    //       const canvas = document.getElementById('waveform');
    //       const ctx = canvas.getContext('2d');

    //       function render(){
    //         requestAnimationFrame(render);
    //         const array = new Uint8Array(analyzer.frequencyBinCount);
    //         analyzer.getByteFrequencyData(array);

    //         ctx.clearRect(0, 0, canvas.width, canvas.height);
    //         ctx.beginPath();
    //         ctx.lineWidth = 2;
    //         ctx.strokeStyle = 'white'
    //         ctx.lineTo(0, canvas.height / 2);
    //         for (let i = 0; i < array.length; i++) {
    //           const x = i * canvas.width / array.length;
    //           const y = canvas.height / 2 - array[i] * canvas.height / 256;
    //           ctx.lineTo(x, y);
    //         }
    //         ctx.lineTo(canvas.width, canvas.height / 2);
    //         ctx.stroke();
    //       }

    //       render();
    //     })
    //     .catch(error => {
    //       console.error(error);
    //     })
    // }
    predict(){
      axios.post('/predict', { prompt: this.prompt}, {responseType: 'blob'})
      .then(response => {
        const blob = new Blob([response.data], { type: 'audio/wav '});
        const audioUrl = URL.createObjectURL(blob);
        const audio = new Audio(audioUrl);
        audio.play();
        this.response = "Audio response played";
        this.prompt = '';
      })
      .catch(error => {
        console.error(error);
      })
    }
  }
}
</script>
