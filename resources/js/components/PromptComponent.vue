<template>
    <div>
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-6 pb-3">
                    <!-- <div class="buttons text-center">
                        <button class="btn btn-outline-light" @click="theAudioWave">Speak</button>
                    </div> -->
                    <div class="form-group d-flex pb-3">
                      <textarea class="form-control" v-model="prompt"></textarea><br>
                      <button class="btn btn-outline-light" style="margin-left: 1rem;">
                        <span>
                          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-mic-fill" viewBox="0 0 16 16">
                            <path d="M5 3a3 3 0 0 1 6 0v5a3 3 0 0 1-6 0z"/>
                            <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5"/>
                          </svg>
                        </span>
                      </button>  
                    </div>
                    <button class="btn btn-outline-light" @click="predict">Talk</button>

                </div>
                <div class="col-lg-6">
                    <div class="border text-light rounded-4">
                        <canvas id="waveform" width="500" height="300"></canvas>
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
      response: '',
      phase: 0
    }
  },
  methods: {
    soundWave(){
      const canvas = document.getElementById('waveform');
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.beginPath();
      ctx.lineWidth = 2;
      ctx.strokeStyle = 'white';
      for (let i = 0; i < canvas.width; i++){
        const y = canvas.height / 2 + Math.sin((i + this.phase) * 0.01) * 100;
        if(i === 0){
          ctx.moveTo(i, y);
        }else{
          ctx.lineTo(i, y);
        }
      }
      ctx.stroke();
      this.phase += 10;
    },
    predict(){
      axios.post('/predict', { prompt: this.prompt})
      .then(response => {
        this.response = response.data.response;
        this.prompt = '';

        if(window.responsiveVoice){
          window.responsiveVoice.speak(this.response, 'Filipino Female', {
            rate: 1.1,
            pitch: 1.1,
            onstart: () => {
              this.intervalId = setInterval(() => {
                this.soundWave();
              }, 20);
            },
            onend: () => {
               clearInterval(this.intervalId);
            }
          })
        }else{
          console.warn('Responsive voice not loaded');
        }
      })
      .catch(error => {
        console.error(error);
      })
    }
  }
}
</script>
