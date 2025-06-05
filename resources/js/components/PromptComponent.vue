<template>
    <div>
        <div class="container text-center">
            <div class="row">
                <div class="col-md-6 pb-3">
                    <div class="text-light rounded-4">
                      <!--The Wave-->
                        <div class="container_audio ">
                            <div class="circle sound-wave-opacity delay1"></div>
                            <div class="circle sound-wave-opacity delay2"></div>
                            <div class="circle sound-wave-opacity delay3"></div>
                            <div class="circle sound-wave-opacity delay4"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 pb-3">
                    <!-- <div class="buttons text-center">
                        <button class="btn btn-outline-light" @click="theAudioWave">Speak</button>
                    </div> -->
                    <div class="prompt_controls" v-if="!isPromptControlsHidden">
                        <div class="form-group d-flex pb-3">
                        <textarea class="form-control" v-model="prompt"></textarea><br>
                        <button class="btn btn-outline-light" style="margin-left: 1rem;" @click="startListening">
                          <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-mic-fill" viewBox="0 0 16 16">
                              <path d="M5 3a3 3 0 0 1 6 0v5a3 3 0 0 1-6 0z"/>
                              <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5"/>
                            </svg>
                          </span>
                        </button>
                      </div>
                      <button class="btn btn-outline-light" @click="generate">Talk</button>
                    </div>

                      <div>
                      <span class="text-light">
                        {{ response }}
                      </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
export default {
  data(){
    return{
      prompt: '',
      response: '',
      phase: 0,
      recognition: null,
      isListening: false,
      cache: {},
      cacheMaxSize: 100,
      cacheTTL: 300000,

      isPromptControlsHidden: true
    }
  },
  mounted(){  
    window.addEventListener('keydown', this.handleKeyDown);
  },  
  methods: {
    startListening(){
      if(!this.isListening){
        this.isListening = true;
        this.recognition = new webkitSpeechRecognition() || new SpeechRecognition();
        this.recognition.lang = 'en-US';
        this.recognition.maxResults = 10;
        this.recognition.onresult = this.onResult;
        this.recognition.onerror = this.onError;
        this.recognition.onend = this.onEnd;
        this.recognition.start();
      }else {
        this.recognition.stop();
        this.isListening = false;
      }
    },  
    onResult(event){
      let transcript = '';
      for(let i = event.resultIndex; i < event.results.length; ++i){
        transcript += event.results[i][0].transcript;
      }
      this.prompt = transcript;
      setTimeout(() => {//auto send after talking or voice recognition
        this.generate();
      }, 500);
    },
    onError(event){
      console.log('Error occured in SpeechRecognition: ' + event.error);
    },
    onEnd(){
      this.isListening = false;
    },
    soundWave(){
      let circle = document.querySelectorAll('.circle');
      for (let i = 0; i < circle.length; i++){
        // circle[i].style.animation = 'waves 2.5s linear';
        // circle[i].style.animationDelay = (i * 0.7) + 's';
        circle[i].classList.add('sound-wave');
        circle[i].classList.remove('sound-wave-opacity');
        circle[i].classList.add('delay' + (i + 1));
      }
    },
    stopSoundWave(){
      let circle = document.querySelectorAll('.circle');
      for (let i = 0; i < circle.length; i++){
        circle[i].classList.remove('sound-wave');
        circle[i].classList.add('sound-wave-opacity');
        circle[i].classList.remove('delay1', 'delay2', 'delay3', 'delay4');
      }
    },
    generate(){
      axios.post('/generate', { prompt: this.prompt})
      .then(response => {
        this.response = response.data.response;
        this.prompt = '';

        if(window.responsiveVoice){
          window.responsiveVoice.speak(this.response, 'UK English Male', {
            rate: 1.3,
            pitch: 1.2,
            onstart: () => {
              this.soundWave();
              this.intervalId = setInterval(() => {
                this.soundWave();
              }, 2000);
            },
            onend: () => {
                clearInterval(this.intervalId);
                this.stopSoundWave();
            }
          })
        }else{
          console.warn('Responsive voice not loaded');
        }
      })
      .catch(error => {
        console.error(error);
      })
    },

    //Shortcut keys
    handleKeyDown(event) {
      if(event.ctrlKey){
        this.startListening();//calls the method startListening
      }else if(event.keyCode === 13){ //numerical code for Enter key
        this.generate();
      }else if(event.keyCode === 36){//numiercal key code for "home"
        if(this.isPromptControlsHidden == false){
            this.isPromptControlsHidden = true;
        }else{
          this.isPromptControlsHidden = false;
        }
      }
    }
  }
}
</script>
