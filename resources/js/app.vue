<template>
    <div>
        <h3>test</h3>
        <h3 v-if="errorMsg">{{ errorMsg }}</h3>
        <div v-for="support in supports" :key="support.id">
            {{ support.user_id }} : {{ support.support_specifics}}
            <hr />
        </div>
    </div>
    <!--<Greet />-->
</template>
<script>
import Greet from '/resources/js/Greet.vue'
import axios from 'axios'
export default {
    name: 'Greet',
    components:{
        Greet,
    },
    created(){
        this.getSupports()
    },
    data(){
        return {
            supports: [],
            errorMsg: '',
            ctr: 0
        }
    },
    methods: {
        getSupports(){
            axios
                //.get('https://jsonplaceholder.typicode.com/posts')
                .get('/posts')
                .then((response) => {
                    //console.log(response.data)
                    this.supports = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.errorMsg='Error Retrieving Data'
                })
        }
    }
}
</script>
