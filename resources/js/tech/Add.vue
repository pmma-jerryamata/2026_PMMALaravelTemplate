<template>
<div class="container px-3 px-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10 col-12 mb-3">
            <div v-if="loading" class="text-center">
                Creating Ticket...<br />
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div v-else class="card bg-light border-0 p-3">
                <h4 v-if="errorMsg" class="alert alert-danger text-center">{{ errorMsg }}</h4>
                <h4 v-if="successMsg" class="alert alert-success text-center">{{ successMsg }}</h4>
                <h1 class="fs-6 fw-bold text-start">Department/Unit:</h1>
                <select v-model="employeeDepartment" class="form-select text-sm text-start" @change="getEmployees(employeeDepartment)">
                    <option v-for="department in departments" :key="department.id" :value="department.id">
                        {{ department.name }}
                    </option>
                </select>

                <h1 class="fs-6 fw-bold text-start mt-3">Employee:</h1>
                <select v-model="employeeName" class="form-select text-sm text-start">
                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                        {{ emp.name }}
                    </option>
                </select>

                <h1 class="fs-6 fw-bold text-start mt-3">Task:</h1>
                <select v-model="taskName" class="form-select text-sm text-start">
                    <option v-for="task in tasks" :key="task.id" :value="task.id">
                        {{ task.name }}
                    </option>
                </select>
                
                <h1 class="fs-6 fw-bold text-start mt-3">Specifics:</h1>
                <textarea v-model="specifics" class="form-control text-sm text-start" rows="4" placeholder="Provide details here."></textarea>
                <div class="d-flex justify-content-center mt-4">
                    <button class="btn btn-secondary me-2 px-3" @click="clearFields()">&nbsp;&nbsp;&nbsp;&nbsp;Clear&nbsp;&nbsp;&nbsp;&nbsp;</button>
                    <button class="btn btn-primary px-4" @click="createTicket()">Submit</button>
                </div>

            </div>
            
        </div>
    </div>
</div>
    
</template>

<script>
export default {
    name: 'Add Ticket',
    props: {
        techId: {
            type: Number,
            required: true,
        },
    },
    
    created(){
        this.getDepartments()
        this.getTasks()
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
            departments: '',
            departmentId: 1,
            employees: [],
            tasks: [],
            employeeDepartment: '',
            employeeName: '',
            taskName: '',
            specifics: '',
            successMsg: '',
            technicianId: this.techId,
            loading: false, // New loading state
        };
    },
    methods:{
        getDepartments(){
            this.resetValues();
            axios.get('/api/get-departments', {
            })
            .then((response) => {
                //console.log(response.data)
                this.departments = response.data;
            })
            .catch((error) => {
                console.log(error)
                this.errorMsg = error
            })
        },
        getEmployees(dept_id){
            this.resetValues();
            axios.get('/api/get-employees', {
                params:{
                    departmentId: dept_id,
                }
            })
            .then((response) => {
                //console.log(response.data)
                this.employees = response.data;
            })
            .catch((error) => {
                console.log(error)
                this.errorMsg = error
            })
        },
        getTasks(){
            axios.get('/api/get-tasks', {
            })
            .then((response) => {
                //console.log(response.data)
                this.tasks = response.data;
            })
            .catch((error) => {
                console.log(error)
                this.errorMsg = error
            })
        },
        createTicket(){
            if(this.checkInput()){
                this.resetValues();
                this.loading = true; // Start loading
                axios.get('/api/create-ticket', {
                    params:{
                        empId: this.employeeName,
                        task: this.taskName,
                        techId: this.technicianId,
                        specificsField: this.specifics
                    }
                })
                .then((response) => {
                    //console.log(response.data)
                    this.successMsg = response.data;
                })
                .catch((error) => {
                    console.log(error)
                    this.errorMsg = error
                })
                .finally(() => {
                    this.loading = false; // Stop loading
                });
                this.employeeDepartment = '';
                this.employeeName = '';
                this.taskName = '';
                this.specifics = '';
            }
            
        },
        checkInput(){
            //Check technician
            if(this.employeeDepartment == ''){
                this.errorMsg = 'Please select Department/Unit.';
                return false;
            }
            if(this.employeeName == ''){
                this.errorMsg = 'Please select an Employee.';
                return false;
            }
            if(this.taskName == ''){
                this.errorMsg = 'Please select a Task.';
                return false;
            }
            return true;
        },
        resetValues(){
            this.errorMsg = '';
            this.successMsg = '';
        },
        clearFields(){
            this.taskName = '';
            this.specifics = '';
            this.employeeName = '';
            this.employeeDepartment = '';
        },
    },
}
</script>