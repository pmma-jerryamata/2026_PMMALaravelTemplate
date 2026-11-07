<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <header class="py-3">
    <!--<div class="container px-lg-5">
        <div class="p-lg-1 bg-light rounded-3 text-center">
        -->
        <div>
            <div align="left">
            <h1 class="display-8 fw-bold">DELETE EMPLOYEE RECORD</h1>
            <br />
            <table border="2" width="100%" id="resetStyle">
                <tbody>
                <tr style="border:2px solid black"> 
                <td width="40%" style="border:2px solid black">
                    <table align='center'>
                        <tbody>
                        <tr>
                            <td align="right"><h4>Employee :&nbsp;&nbsp;</h4></td>
                            <td><h4><input name="empName" type="text" size="25" @keyup= searchEmployee($event) ></h4>
                            </td>
                        </tr>
                        <tr>
                            <td align="left" colspan="2">
                                <div v-for="result in searchResult" :key= "result">
                                    <input type="radio" id= {{result.id}} name= {{result.id}} @click= selectEmployee($event) :value= result.id > <lable style= "font-size:24px" for= {{result.id}}>{{result.name}}</lable>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td width="60%">
                    <br/>
                    <table align='center'>
                        <tbody>
                        <tr>
                            <td colspan="2" align="center">
                                <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Name :&nbsp;&nbsp;</h4></td>
                            <td align="left"><h4><input name="name" v-model="employeeName" type="text" size="50" disabled autocomplete="off"></h4></td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Email :&nbsp;&nbsp;</h4></td>
                            <td align="left"><h4><input name="e-mail" v-model="employeeEmail" type="text" size="50" disabled></h4></td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Dept./Unit :&nbsp;&nbsp;</h4></td>
                            <td align="left"><h4>
                                <select name="dept" v-model="employeeDepartment" style="font-size:24px" disabled>
                                <template v-for="department in selectResult.departments" :key= "department"> 
                                    <option v-if="department.id === selectResult.employee[0].department_id" selected="selected" :value= department.id>{{ department.name }}</option>
                                    <option v-else :value= department.id>{{ department.name }}</option> 
                                </template>
                                </select></h4>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <button class="btn btn-primary btn-lg" :disabled = buttonDelete @click= deleteEmployee()>Delete</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br />
                </td></tr>
                </tbody>
            </table>
            
            
            
        </div>
    </div>
</header>
    
</template>

<script>

import axios from 'axios';
export default {
    name: 'Remove',
    

    created(){
        //this.searchEmployee();
    },
    data(){
        return{
            errorMsg: '',
            successMsg: '',
            searchTest: 'Search Test',
            searchValue: '',
            searchResult: [],
            selectValue: '',
            selectResult: [],
            buttonDelete: true,
            employeeId: '',
            employeeName: '',
            employeeEmail: '',
            employeeDepartment: 0,
            updateResult: [],
        };
    },
    methods:{
        searchEmployee(e){
            this.errorMsg = '';
            this.successMsg = '';
            this.searchTest = e.target.value;
            this.searchValue = e.target.value;
                if(e.target.value){ //if input field is not empty
                    axios.get('/api/search', {
                    params:{
                        searchVal: this.searchValue,
                    }
                    })
                    .then((response) => {
                        //console.log(response.data)
                        this.searchResult = response.data;
                    })
                    .catch((error) => {
                        console.log(error)
                        this.errorMsg = error
                    })
                }else{
                    this.searchResult = [];
                    this.buttonDelete = true;
                    this.employeeName = '';
                    this.employeeEmail = '';
                    this.employeeDepartment = '';
                }
            
        },
        selectEmployee(e){
            this.selectValue = e.target.value;
            axios.get('/api/select-employee', {
                    params:{
                        searchVal: this.selectValue,
                    }
                    })
                    .then((response) => {
                        //console.log(response.data)
                        this.selectResult = response.data;
                        this.buttonDelete = false;
                        this.employeeId = this.selectResult.employee[0].id,
                        this.employeeName = this.selectResult.employee[0].name;
                        this.employeeEmail = this.selectResult.employee[0].email;
                        this.employeeDepartment = this.selectResult.employee[0].department.id;
                    })
                    .catch((error) => {
                        console.log(error)
                        this.errorMsg = error
                    })
        },
        deleteEmployee(){
            axios.get('/api/remove', {
                    params:{
                        id: this.employeeId,
                    }
                    })
                    .then((response) => {
                        //console.log(response.data)
                        this.successMsg = response.data;
                        this.employeeId = '',
                        this.employeeName = '';
                        this.employeeEmail = '';
                        this.employeeDepartment = '';
                        this.buttonDelete = true;
                    })
                    .catch((error) => {
                        console.log(error)
                        this.errorMsg = error
                    })
        },
    },
}
</script>
