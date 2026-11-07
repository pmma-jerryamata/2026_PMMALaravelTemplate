<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <button style="background-color: #008CBA; border-color: #008CBA;" @click= getTasksPending()><h3>PENDING ({{ countPending }})</h3></button>
    <button style="background-color: #04AA6D; border-color: #04AA6D;" @click= getTasks() ><h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ALL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3></button>
    <button style="background-color: #f44336; border-color: #f44336;" @click= getTasksCancelled()><h3>CANCELLED</h3></button>
    <button style="background-color: #9888EB; border-color: #9888EB;" @click= getTasksCompleted()><h3>COMPLETED</h3></button>
    <button style="background-color: yellow; border-color: yellow;" @click= addTask()><h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ADD&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3></button>
    

    <table class="table table-responsive table-xs">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" :style="styleHeader">
                    {{column.label}}
                </th>
            </tr>
        </thead>
        <tbody>
        <tr v-for="task in tasks.supp" :key= "task">
             <!--<form action="{{ route('edit') }}" method="POST">                                                                                                                                                      
            @csrf-->
            <td>{{ task.id }}</td>
            <td>{{ moment(task.created_at).format("MM-DD-YY HH:mm") }}</td>
            <td>{{ task.employee.name }}</td>
            <td>{{ task.employee.department.name }}</td>
            <td><a v-if="showFindings" href="#" @click.prevent="toggleFindings(task.id)"> {{ task.task.name }} - {{ task.support_specifics }} </a>
                <br />  <template v-if="activeTaskId === task.id">
                            <div> {{ task.findings }} </div>
                        </template>
            </td>
            <td><select v-if="task.technician?.name === ''"  :name= task.id :id= task.id :disabled = statusTech :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech"> 
                    <!--<option v-if="tech.name === task.technician.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else-if="tech.name === ''" selected="selected"></option>
                    <option v-else>{{ tech.name }}</option>-->
                     <!-- Use if technician id is nullable-->
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option> 
                     
                </template>
                </select>
                <select v-else :name= task.id :id= task.id :disabled = statusTech  :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech"> 
                    <!--<option v-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else-if="tech.name === ''" selected="selected"></option>
                    <option v-else>{{ tech.name }}</option>-->
                    <!-- Use if technician id is nullable-->
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option>
                    
                </template>
                </select>
            </td>
            <td>
                <!--<input type="datetime-local" value= "2024-03-26T05:55" />-->
                <input v-if="task.created_at === task.updated_at" type="datetime-local" :disabled = statusTech>
                <input v-else type="datetime-local" v-bind:value='moment(task.updated_at).format("YYYY-MM-DD HH:mm")' :disabled = statusTech>
            </td>
            <!--<td>{{ task.status }}<br />&nbsp;<a href="#" @click= clickEdit(task)><i title="Edit" class="bi bi-pencil-square"></i></a>&nbsp;&nbsp;<a href="#"><i title="Save Changes" class="bi bi-disc"></i></a></td>-->
            <td>
            <form action= "/edit"  method="GET">
                <div>{{ task.status }}<br />
                <button :id= task.id :name= task.id type="submit"><i title="Edit" class="bi bi-pencil-square"></i></button></div>
                <input type=hidden :value= task.id id="passValue" name="passValue">
            </form>
            </td>
            <!--</form>-->
        </tr>
        </tbody>
    </table>
    
</template>

<script>

import axios from 'axios';
import moment from 'moment';
export default {
    name: 'Greet',
    countPending: 0,

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
                {label: '#', name: 'number'},
                {label: 'Request Date', name: 'req_date'},
                {label: 'Request From', name: 'req_from'},
                {label: 'Department/Unit', name: 'department'},
                {label: 'Task - Specifics', name: 'task'},
                {label: 'Technician', name: 'technician'},
                {label: 'Date Completed', name: 'com_date'},
                {label: 'Status', name: 'status'},
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
            showFindings: false,
            activeTaskId: null,

            currentPage: 1,
            pageSize: 10, // Number of tasks per page
            totalTasks: 0, // Will be updated from API response
        };
    },
    methods:{
        toggleFindings(taskId) {
             // Toggle the visibility of the template for the clicked task
            this.activeTaskId = this.activeTaskId === taskId ? null : taskId;
        },

        getTasks(page = 1){
            this.showFindings = true;
            this.styleHeader.backgroundColor = '#04AA6D';
            axios.get('/api/support', {
                params:{
                    order: this.orderBy,
                    page: page,
                    pageSize: this.pageSize,
                }
            })
             .then((response) => {
                console.log(response.data)
                //this.tasks = response.data;
                //this.orderBy[0].value = this.orderBy[0].value + 1;
                
               /*
                this.tasks = response.data.tasks; // Ensure your API returns paginated tasks
                this.totalTasks = response.data.total; // Total tasks from API response
                this.currentPage = page;

                this.orderBy[0].value = this.orderBy[0].value + 1;
                */
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        
        getTasksPending(){
            this.showFindings = true;
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
        getTasksCompleted(){
            this.styleHeader.backgroundColor = '#9888EB';
            this.showFindings = true;
            axios.get('/api/supportCompleted', {
                params:{
                   order: this.orderBy
                }
            })
             .then((response) => {
                //console.log(response.data)
                this.tasks = response.data
                this.orderBy[3].value = this.orderBy[3].value + 1;
                //this.sortOrder()
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        getTasksCancelled(){
            this.showFindings = true;
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
        /*sortOrder(){
            if(this.orderBy === 'desc'){
                this.orderBy = 'asc'
            }else{
                this.orderBy = 'desc'
            }
        },*/
        addTask(){
            window.location.href = '/add';
        },
        setAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                this.refreshPage();
            }, 300000); // 30000 milliseconds = 30 seconds
        },
        refreshPage() {
            window.location.reload();
        },
    },
}
</script>
