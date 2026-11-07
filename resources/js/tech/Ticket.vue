<template>
    <div class="container px-lg-5" align='center'>
        <div class="col-lg-6 col-xxl-5 mb-3"> 
            <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
            <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
        </div>
    </div>
    <template v-for="task in tasks.supp" :key= "task">
    <div class="container px-lg-5" align='center'>
        <div :id= "'button' + task.id" :name= "'button' + task.id"></div>
        <div class="col-lg-6 col-xxl-5 mb-3">     
            <div v-if="loading" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div v-else class="card bg-light border-2 h-100 fs-6 fw-bold" align='left'>
                <h1 class="fs-4 fw-bold" :style="techId === task.technician?.id || task.technician === null ? { backgroundColor: '#39ff14' } : {}">
                    <a @click= showFindings(task.id)><i class="lni lni-ticket"></i>
                    <u style="color:blue"><font color='blue'>{{ task.id }} : {{ moment(task.created_at).format("MM-DD-YY HH:mm") }}</font></u>
                </a></h1>
                <p class="fs-6"><b>
                    {{ task.employee.name }}
                </b>
                <br />
                    {{ task.employee.department.name }}
                </p>
                <p class="fs-6">
                    {{ task.task.name }} - {{ task.support_specifics }}
                </p>
                <div  :id= task.id :name= task.id style="width:100%; display:none;" align='center'>     
                    <hr style="height:3px;border-width:0;color:black;background-color:black">
                    <h1 class="fs-6 fw-bold">Findings/Action Taken:</h1>
                    <textarea :id= "'findings'+task.id" class="fs-6" style="width:97%;" align='center' :placeholder= "'Input findings for ticket #: '+task.id"></textarea>
                    <h2 v-if="submitPrompt" style="background-color: #ff0000">{{submitPrompt}}</h2>
                    <select :name= "'tech'+task.id" :id= "'tech'+task.id" :ref= "'tech'+task.id" class="fs-6" style="width:97%" align='center'>
                        <option value='' selected="selected">--SELECT TECHNICIAN--</option>
                        <template v-for="tech in tasks.tech" :key= "tech">
                            <option v-if="tech.name === task.technician?.name" selected="selected" :value= tech.id>{{ tech.name }}</option>
                            <option v-else :value= tech.id>{{ tech.name }}</option> 
                        </template>
                    </select>
                    <h1 class="fs-6 fw-bold">Ticket Status:</h1>
                    <select :name= "'status'+task.id" :id= "'status'+task.id" :ref= "'status'+task.id" class="fs-6" style="width:97%" align='center'>
                            <option value='Pending'>Pending</option>
                            <option selected="selected" value='Completed'>Completed</option>
                            <option value='Cancelled'>Cancelled</option>
                    </select>
                    <br /><br />
                    <button class="btn btn-primary btn-lg" @click= supportDone(task.id)>Submit</button>&nbsp;
                </div>
                    <br />
            </div> 
        </div>
    </div>
    </template>
</template>

<script>

import axios from 'axios';
import moment from 'moment';
export default {
    name: 'Tech Ticket',
    props: {
        techId: {
            type: Number,
            required: true,
        },
    },
    created(){
        this.getTasksPending()
    },
    mounted(){
        //this.setAutoRefresh()
    },
    beforeDestroy(){
        //this.clearAutoRefresh()
    },
    data(){

        return{
            tasks: [],
            errorMsg: '',
            successMsg: '',
            orderBy: [{lable: 'pending', value: 0}],
            moment: moment,
            prevId: '',
            technician: '',
            submitPrompt: '',
            //username: '',
            loading: false, // New loading state
        };
    },
    methods:{
        supportDone(task){
            this.submitPrompt = '';
            //this.technician = document.getElementById('tech'+task).value;
               axios.get('/api/supportUpdate', {
                params:{
                    taskId: task,
                    techId: document.getElementById('tech'+task).value,
                    status: document.getElementById('status'+task).value,
                    findings: document.getElementById('findings'+task).value,
                }
                })
                .then((response) => {
                    //console.log(response.data)
                    this.successMsg = response.data;
                    this.$forceUpdate();
                })
                .catch((error) => {
                    console.log(error)
                    this.errorMsg = error;
                })
            
            this.prevId = '';
            this.getTasksPending();
        },
        showFindings(task){
            this.submitPrompt = '';
            this.successMsg = '';
            this.errorMsg = '';
            if(this.prevId){
                document.getElementById(this.prevId).style.display = "none";
            }
            this.prevId = task;
            document.getElementById(task).style.display = "block";
            document.getElementById('button'+task).scrollIntoView();
            document.getElementById('findings'+task).focus();
        },
        getTasksPending(){
            //this.username = document.getElementById('username').value;
            this.submitPrompt = '';
            this.errorMsg = '';
            this.successMsg = '';
            this.loading = true; // Start loading
            axios.get('/api/supportPendingTech', {
                params:{
                    order: this.orderBy
                }
            })
             .then((response) => {
                //console.log(response.data)
                this.tasks = response.data
            })
            .catch((error) => {
                console.log(error)
                this.errorMsg = error
            })
            .finally(() => {
                this.loading = false; // Stop loading
            });
        },
    },
}


</script>