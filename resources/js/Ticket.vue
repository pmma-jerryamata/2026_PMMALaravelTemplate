<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <button style="background-color: #008CBA; border-color: #008CBA;" @click= getTasksPending()><h3>ON-GOING&nbsp;({{ countPending }})</h3></button>
    <!--
    <button style="background-color: #f44336; border-color: #f44336;" @click= getTasksCancelled()><h3>CANCELLED</h3></button>
    -->
    <table class="table table-responsive table-xs">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" :style="styleHeader" :class="column.class">
                    {{column.label}}
                </th>
            </tr>
        </thead>
        <tbody>
        <tr v-for="task in tasks.supp" :key= "task">
            <td align="center" class="mobile-bg"><b> <label style="display:none;" class="mobile-show">Ticket #: </label> {{ task.id }} </b></td>
            <td style="background-color: #FFFFFF">{{ moment(task.created_at).format("MM-DD-YY HH:mm") }}</td>
            <td style="background-color: #FFFFFF">{{ task.employee.department.name }}</td>
            <td style="background-color: #FFFFFF">{{ task.task.name }} <span class="mobile-hide" > - {{ task.support_specifics }} </span></td>
            
            <td style="background-color: #FFFFFF" class="mobile-hide">
                <select v-if="task.technician?.name === ''"  :name= task.id :id= task.id :disabled = statusTech :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech"> 
                    <option v-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else-if="tech.name === ''" selected="selected"></option>
                    <option v-else>{{ tech.name }}</option> 
                </template>
                </select>
                
                <select v-else :name= task.id :id= task.id :disabled = statusTech  :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech"> 
                    <option v-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else-if="tech.name === ''" selected="selected"></option>
                    <option v-else>{{ tech.name }}</option>
            
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option> 
                </template>
                </select>
            </td>
            
        </tr>
        </tbody>
    </table>
    
</template>

<script>

import axios from 'axios';
import moment from 'moment';
export default {
    name: 'Greet',
    
    created(){
        this.getTasksPending()
        /*this.getTechnicians()*/
    },
    mounted(){
        this.setAutoRefresh()
    },
    beforeDestroy(){
        this.clearAutoRefresh()
    },
    data(){
        let sortOrders = {};
        let columns = [
                {label: 'Ticket No.', name: 'number', class: ''},
                {label: 'Request Date', name: 'req_date', class: ''},
                {label: 'Department/Unit', name: 'department', class: ''},
                {label: 'Task - Specifics', name: 'task', class: ''},
                {label: 'Attending Technician', name: 'technician', class: 'mobile-hide' },
            ];
            columns.forEach((column) => {
                sortOrders[column.name] = 1;
            });
        return{
            tasks: [],
            errorMsg: '',
            moment: moment,
            statusTech: true, 
            columns: columns,
            sortKey: 'req_date',
            sortOrders: sortOrders,
            orderBy: [{lable: 'all', value: 0},{lable: 'pending', value: 0},{lable: 'cancelled', value: 0},{lable: 'completed', value: 0}],
            styleHeader: {
                backgroundColor: '#04AA6D',
            },
            refreshInterval: null,
            countPending: 0,
        };
    },
    methods:{
        getTasksPending(){
            this.styleHeader.backgroundColor = '#008CBA';
            axios.get('/api/supportPending', {
                params:{
                    order: this.orderBy
                }
            })
             .then((response) => {
                //console.log(response.data)
                this.tasks = response.data
                this.countPending = this.tasks.size;
                this.orderBy[1].value = this.orderBy[1].value + 1;
                //this.sortOrder()
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        getTasksCancelled(){
            this.styleHeader.backgroundColor = '#f44336';
            axios.get('/api/supportCancelled', {
                params:{
                    order: this.orderBy
                }
            })
             .then((response) => {
                //console.log(response.data)
                this.tasks = response.data
                this.orderBy[2].value = this.orderBy[2].value + 1;
                //this.sortOrder()
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        sortOrder(){
            if(this.orderBy === 'desc'){
                this.orderBy = 'asc'
            }else{
                this.orderBy = 'desc'
            }
        },
        setAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                this.refreshPage();
            }, 60000); // 30000 milliseconds = 30 seconds
        },
        clearAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        },
        refreshPage() {
            window.location.reload();
        }
    },
}
</script>
