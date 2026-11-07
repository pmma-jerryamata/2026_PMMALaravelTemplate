<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <header class="py-3">
    <!--<div class="container px-lg-5">
        <div class="p-lg-1 bg-light rounded-3 text-center">
        -->
        <div>
            <div align="left">
            <h1 class="display-8 fw-bold">CREATE EMPLOYEE RECORD</h1>
            <br />
            <table border="2" width="100%" id="resetStyle" align="left">
                <tbody>
                <tr style="border:none">
                    <td style="border:none">
                        <br />
                    </td>
                </tr>
                <tr> 
                <td width="40%">
                    <!-----------------------------------------//////////////////////////////////---------------------------------------------------->
                    <table align='left' width="40%">
                        <tbody>
                        <tr>
                            <td colspan="2" align="center">
                                <h2 v-if="successMsg" style="background-color: #39FF14">{{ successMsg }}</h2>
                                <h2 v-if="creationPrompt" style="background-color: #ff0000">{{ creationPrompt }}</h2>
                                
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Name :&nbsp;&nbsp;</h4></td>
                            <td><h4><input type="text" v-model="employeeName" size="30" id="employeeName" name="employeeName" @keyup= searchEmployee($event) placeholder="LASTNAME, FIRSTNAME"></h4>
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Email :&nbsp;&nbsp;</h4></td>
                            <td><h4><input name="email" type="email" v-model="employeeEmail" size="30" placeholder="abc.lastname@pmma.edu.ph" autocomplete="off"></h4>
                            </td>
                        </tr>
                        <tr>
                            <td align="right"><h4>Dept./Unit :&nbsp;&nbsp;</h4></td>
                            <td align="left"><h4>
                                <select name="departments" v-model="employeeDepartment" style="font-size:15px">
                                <template v-for="department in departments" :key= "department"> 
                                    <option :value= department.id>{{ department.name }}</option> 
                                </template>
                                </select></h4>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <button class="btn btn-primary btn-lg" @click= clearFields()>&nbsp;&nbsp;Clear&nbsp;&nbsp;</button>&nbsp;
                                <button class="btn btn-primary btn-lg" @click= addEmployee()>Submit</button>
                                
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-----------------------------------------//////////////////////////////////---------------------------------------------------->
                    <!-----------------------------------------//////////////////////////////////---------------------------------------------------->
                    <table align='center'>
                        <tbody>
                        <tr>
                            <td align="right" style="border:4px solid green"><h4>&nbsp;DATABASE CHECKER :&nbsp;&nbsp;</h4></td>
                        </tr>
                        <tr>
                            <td align="left">
                                <div v-for="result in searchResult" :key= "result">
                                    <lable style= "font-size:24px">{{result.name}}</lable>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-----------------------------------------//////////////////////////////////---------------------------------------------------->
                </td>
                </tr>
                </tbody>
            </table>
            
            
            
        </div>
    </div>
</header>
    
</template>

<script>

import axios from 'axios';
export default {
    name: 'Create',
    
    created(){
        this.getDepartments();
    },
    data(){
        return{
            errorMsg: '',
            successMsg: '',
            searchValue: '',
            searchResult: [],
            departments: [],
            employeeName: '',
            employeeEmail: '',
            employeeDepartment: '',
            creationPrompt: '',
        };
    },
    methods:{
        searchEmployee(e){
            this.errorMsg = '';
            this.successMsg = '';
            this.creationPrompt = '';
            this.searchValue = e.target.value.toUpperCase();
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
                }
            
        },
        getDepartments(){
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
        addEmployee(){
            if(this.checkInput())
            {
               axios.get('/api/add', {
                params:{
                    empName: this.employeeName.toUpperCase(),
                    empEmail: this.employeeEmail.toLowerCase(),
                    empDepartment: this.employeeDepartment, 
                }
                })
                .then((response) => {
                    //console.log(response.data)
                    this.successMsg = response.data;
                    this.employeeName = '';
                    this.employeeEmail = '';
                    this.employeeDepartment = '';
                })
                .catch((error) => {
                    //console.log(error)
                    this.errorMsg = error;
                })
            }
            
        },
        clearFields(){
            this.errorMsg = '';
            this.successMsg = '';
            this.employeeName = '';
            this.employeeEmail = '';
            this.employeeDepartment = '';
            this.creationPrompt = '';
        },
        checkInput(){
            //Check format of email
            if(this.employeeName == '' || this.employeeEmail == '' || this.employeeDepartment == ''){
                this.creationPrompt = 'Please fill-in all fields.';
                return false;
            }
            if(!this.validEmail(this.employeeEmail)){
                this.creationPrompt = 'Invalid email format.';
                return false;
            }
            return true;
        },
        validEmail(email){
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }
    },
}
</script>
