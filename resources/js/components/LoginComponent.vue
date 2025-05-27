<template>
    <div>
        <div class="login_container p-3">
            <div class="row pt-5">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label text-light">Name</label>
                        <input type="text" class="form-control" placeholder="Enter Name" v-model="data.name">
                    </div>
                </div>
                <div class="col-md-12 pt-3">
                    <div class="form-group">
                        <label class="form-label text-light">Password</label>
                        <input type="password" class="form-control" placeholder="Enter Password" v-model="data.password">
                    </div>
                </div>
            </div>
            <div class="row pt-5">
                <div class="buttons text-center">
                    <button class="btn btn-outline-light" @click="login">Login</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default{
    data(){
        return{
            data: {
                name: '',
                password: ''
            }
        }
    },
    methods:{
        login(){
            axios.post('/prompt/login', this.data)
            .then(response => {
                console.log(response.data)
                window.location.href = '/prompt';
            })
            .catch(error => {
                console.log(error);
            })
        }
    }

}
</script>