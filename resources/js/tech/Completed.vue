<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
    <div class="container px-lg-5" align='center'>
        <div class="col-lg-6 col-xxl-5 mb-3">         
            <div class="card bg-light border-0 h-100" align='left'>
                DEVELOPMENT IN PROGRESS..
                <br />
                <br />
                {{ tasks }}
                <!--<h1 class="fs-6 fw-bold">
                    Department/Unit:
                </h1>
                <p class="fs-6">
                    <select v-model="employeeDepartment" style="font-size:13px; width:100%" @change= getEmployees(employeeDepartment)>
                        <template v-for="department in departments" :key= "department"> 
                            <option :value= department.id>{{ department.name }}</option> 
                        </template>
                    </select>
                </p>
                <h1 class="fs-6 fw-bold">
                    Employee:
                </h1>
                <p class="fs-6">
                    <select v-model="employeeName" style="width:100%; font-size:13px">
                        <template v-for="emp in employees" :key= "emp">
                            <option :value= emp.id>{{ emp.name }}</option> 
                        </template>
                    </select>
                </p>
                <h1 class="fs-6 fw-bold">
                    Task:
                </h1>
                <p class="fs-6">
                    <select v-model="taskName" style="width:100%; font-size:13px">
                        <template v-for="task in tasks" :key= "task">
                            <option :value= task.id>{{ task.name }}</option> 
                        </template>
                    </select>
                </p>
                <h1 class="fs-6 fw-bold">
                    Specifics:
                </h1>
                <p class="fs-6">
                    <textarea v-model="specifics" style="width:100%; font-size:13px;"></textarea>
                </p>
                <p class="fs-6" align="center">
                    <button class="btn btn-primary btn-lg fs-6" @click= createTicket()>Submit</button>&nbsp;&nbsp;
                
                    <button class="btn btn-primary btn-lg fs-6" @click= clearFields()>&nbsp;&nbsp;Clear&nbsp;&nbsp;</button>
                </p>   
            -->      
            </div> 
        </div>
    </div>
    
</template>

<script>

export default {
    name: 'Completed',
    
    created(){
        this.getTasksCompleted()
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
            tasks: [],
            successMsg: '',
            orderBy: [{lable: 'all', value: 0},{lable: 'pending', value: 0},{lable: 'cancelled', value: 0},{lable: 'completed', value: 0}],
        };
    },
    methods:{
        getTasksCompleted(){
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
    },
}
</script>