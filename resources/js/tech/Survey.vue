<template>
    Survey Result
    <br />
    <!-- {{ outputSupport }} -->
    <div align="left">Overall</div>
    <div id='overall' name='overall'>
        <hr>
        <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
        <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
        <table border="2" width="70%" align="center">
            <tbody>
            <tr style="border: 1px solid black;">
                <td width="20%" style="border: 1px solid black; background-color: lightblue; color: black;">
                    
                </td>
                <template v-for="rating in ratings" :key= "rating">                 
                <td style="font-size:15px; border: 1px solid black; background-color: lightblue; color: black;" width="13.3%" >
                    {{ rating.name }} <br /> ( {{ rating.id }} )
                </td>
                </template>
            </tr>
            <template v-for="ratingMeasure in ratingsMeasure" :key= "ratingMeasure">                 
                <tr style="border: 1px solid black;">
                    <td style="font-size:15px; border: 1px solid black" width="20%">
                        {{ ratingMeasure.name }}
                    </td>
                    <template v-for="ratingCount in ratingsCount[ratingMeasure.id]" :key= "ratingCount">                 
                    <td style="font-size:15px; border: 1px solid black" width="13.3%%">
                        {{ ratingCount }}
                    </td>
                    </template>
                </tr>
            </template>
            </tbody>
        </table>
        <hr>
    </div>
    <br />
    <div align="left">By Type of Support</div>
    <div id='bySupport' name='bySupport'>
        <hr>
        <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
        <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
        <table border="2" width="70%" align="center">
            <tbody>
            <tr style="border: 1px solid black;">
                <td width="20%" style="border: 1px solid black; background-color: lightblue; color: black;">
                    
                </td>
                <template v-for="rating in ratings" :key= "rating">                 
                <td style="font-size:15px; border: 1px solid black; background-color: lightblue; color: black;" width="13.3%" >
                    {{ rating.name }} <br /> ( {{ rating.id }} )
                </td>
                </template>
            </tr>
            <template v-for="ratingMeasure in ratingsMeasure" :key= "ratingMeasure">                 
                <tr style="border: 1px solid black;">
                    <td style="font-size:15px; border: 1px solid black" width="20%">
                        {{ ratingMeasure.name }}
                    </td>
                    <template v-for="ratingCount in ratingsCount[ratingMeasure.id]" :key= "ratingCount">                 
                    <td style="font-size:15px; border: 1px solid black" width="13.3%%">
                        {{ ratingCount }}
                    </td>
                    </template>
                </tr>
            </template>
            </tbody>
        </table>
        <hr>
    </div>
    <br />
    <div align="left">By Technician</div>
    <div id='byTechnician' name='byTechnician'>
        <hr>
        <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
        <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
        <table border="2" width="70%" align="center">
            <tbody>
            <tr style="border: 1px solid black;">
                <td width="20%" style="border: 1px solid black; background-color: lightblue; color: black;">
                    
                </td>
                <template v-for="rating in ratings" :key= "rating">                 
                <td style="font-size:15px; border: 1px solid black; background-color: lightblue; color: black;" width="13.3%" >
                    {{ rating.name }} <br /> ( {{ rating.id }} )
                </td>
                </template>
            </tr>
            <template v-for="ratingMeasure in ratingsMeasure" :key= "ratingMeasure">                 
                <tr style="border: 1px solid black;">
                    <td style="font-size:15px; border: 1px solid black" width="20%">
                        {{ ratingMeasure.name }}
                    </td>
                    <template v-for="ratingCount in ratingsCount[ratingMeasure.id]" :key= "ratingCount">                 
                    <td style="font-size:15px; border: 1px solid black" width="13.3%%">
                        {{ ratingCount }}
                    </td>
                    </template>
                </tr>
            </template>
            </tbody>
        </table>
        <hr>
    </div>
</template>

<script>

import axios from 'axios';
export default {
    name: 'Survey Form',
    
    created(){
        this.getSurvey(),
        this.getBySupport()
    },
    mounted(){
        //this.setAutoRefresh()
    },
    beforeDestroy(){
        //this.clearAutoRefresh()
    },
    data(){

        return{
            errorMsg: '',
            successMsg: '',
            output: [],
            ratings: [],
            ratingsCount: [],
            ratingsMeasure: [],
            outputSupport: [],
        };
    },
    methods:{
        getSurvey(){
            axios.get('/api/get-survey', {
                    /*params:{
                        taskId: task,
                    }*/
                })
                .then((response) => {
                    //console.log(response.data)
                    this.output = response.data;
                    this.ratingsCount = this.output[0];
                    this.ratings = this.output[1];
                    this.ratingsMeasure = this.output[2];
                })
                .catch((error) => {
                    console.log(error)
                    this.errorMsg = error;
                    //this.errorMsg = "Survey table is empty.";
                })
        },
        getBySupport(){
            axios.get('/api/get-survey-by-support', {
                    /*params:{
                        taskId: task,
                    }*/
                })
                .then((response) => {
                    //console.log(response.data)
                    this.outputSupport = response.data;
                })
                .catch((error) => {
                    console.log(error)
                    this.errorMsg = error;
                    //this.errorMsg = "Survey table is empty.";
                })
        },        
        
    },
}


</script>