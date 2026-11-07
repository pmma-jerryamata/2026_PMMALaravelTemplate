<template>
    <h2 v-if="errorMsg" style="background-color: #ff0000">{{ errorMsg }}</h2>
    <nav class="nav nav-pills d-flex">
        <a
        v-for="(link, index) in links"
        :key="index"
        :href="link.href"
        class="nav-link flex-grow-1 text-center"
        :class="{ active: activeIndex === index, disabled: link.disabled }"
        @click.prevent="setActive(index)"
        >
        {{ link.text }}
        </a>
        <a href="/add" class="nav-link flex-grow-1 text-center">ADD</a>
    </nav>

    <table class="table table-responsive table-xs">
        <thead>
            <tr>
                <th v-for="column in columns" :key="column.name" :style="styleHeader">
                    {{column.label}}
                </th>
            </tr>
        </thead>
        
        <tbody>
        <tr v-for="task in tasks" :key= "task">
            <td align="center" class="mobile-bg"><b> <label style="display:none;" class="mobile-show">Ticket #: </label> {{ task.id }} </b></td>
            <td>{{ moment(task.created_at).format("MM-DD-YY HH:mm") }}</td>
            <td>{{ task.employee.name }}</td>
            <td>{{ task.employee.department.name }}</td>
            <td><a v-if="showFindings" href="#" @click.prevent="toggleFindings(task.id)"> {{ task.task.name }} - {{ task.support_specifics }} </a>
                <br />  <template v-if="activeTaskId === task.id">
                            <div> {{ task.findings }} </div>
                        </template>
            </td>
            <td><select v-if="task.technician?.name === ''"  :name= task.id :id= task.id :disabled = statusTech :ref= task.id>
                <template v-for="tech in technicians" :key= "tech">
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option> 
                     
                </template>
                </select>
                <select v-else :name= task.id :id= task.id :disabled = statusTech  :ref= task.id>
                <template v-for="tech in technicians" :key= "tech">
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option>
                    
                </template>
                </select>
            </td>
            <td>
                <input v-if="task.created_at === task.updated_at" type="datetime-local" :disabled = statusTech>
                <input v-else type="datetime-local" v-bind:value='moment(task.updated_at).format("YYYY-MM-DD HH:mm")' :disabled = statusTech>
            </td>
            <td>
            <form action= "/edit"  method="GET">
                <div>{{ task.status }}<br />
                <button :id= task.id :name= task.id type="submit"><i title="Edit" class="bi bi-pencil-square"></i></button></div>
                <input type=hidden :value= task.id id="passValue" name="passValue">
            </form>
            </td>
        </tr>
        </tbody>
        <!--
            <td><select v-if="task.technician?.name === ''"  :name= task.id :id= task.id :disabled = statusTech :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech">
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option> 
                     
                </template>
                </select>
                <select v-else :name= task.id :id= task.id :disabled = statusTech  :ref= task.id>
                <template v-for="tech in tasks.tech" :key= "tech">
                    <option v-if="task.technician === null" selected="selected"></option>
                    <option v-else-if="tech.name === task.technician?.name" selected="selected">{{ tech.name }}</option> 
                    <option v-else>{{ tech.name }}</option>
                    
                </template>
                </select>
            </td>
            <td>
                <input v-if="task.created_at === task.updated_at" type="datetime-local" :disabled = statusTech>
                <input v-else type="datetime-local" v-bind:value='moment(task.updated_at).format("YYYY-MM-DD HH:mm")' :disabled = statusTech>
            </td><td>
            <form action= "/edit"  method="GET">
                <div>{{ task.status }}<br />
                <button :id= task.id :name= task.id type="submit"><i title="Edit" class="bi bi-pencil-square"></i></button></div>
                <input type=hidden :value= task.id id="passValue" name="passValue">
            </form>
        </tr>
        </tbody>
        -->
    </table>


    <div class="pagination" v-show="activeIndex ===1">
        <button :disabled="currentPage === 1" @click="getTasks(currentPage - 1)">Previous</button>
        <span>Page</span>
        <input 
            type="number" 
            v-model.number="inputPage" 
            @keyup.enter="jumpToPage" 
            min="1" 
            :max="totalPages"
            style="width: 50px; text-align: center;"
        />
        <span>of {{ totalPages }}</span>

        <button :disabled="currentPage >= totalPages" @click="getTasks(currentPage + 1)">Next</button>
    </div>
    <div class="pagination" v-show="activeIndex ===0">
        <button :disabled="currentPage === 1" @click="getTasksPending(currentPage - 1)">Previous</button>
        <span>Page</span>
        <input 
            type="number" 
            v-model.number="inputPage" 
            @keyup.enter="jumpToPage" 
            min="1" 
            :max="totalPages"
            style="width: 50px; text-align: center;"
        />
        <span>of {{ totalPages }}</span>

        <button :disabled="currentPage >= totalPages" @click="getTasksPending(currentPage + 1)">Next</button>
    </div>
    <div class="pagination" v-show="activeIndex ===2">
        <button :disabled="currentPage === 1" @click="getTasksCancelled(currentPage - 1)">Previous</button>
        <span>Page</span>
        <input 
            type="number" 
            v-model.number="inputPage" 
            @keyup.enter="jumpToPage" 
            min="1" 
            :max="totalPages"
            style="width: 50px; text-align: center;"
        />
        <span>of {{ totalPages }}</span>

        <button :disabled="currentPage >= totalPages" @click="getTasksCancelled(currentPage + 1)">Next</button>
    </div>
    <div class="pagination" v-show="activeIndex ===3">
        <button :disabled="currentPage === 1" @click="getTasksCompleted(currentPage - 1)">Previous</button>
        <span>Page</span>
        <input 
            type="number" 
            v-model.number="inputPage" 
            @keyup.enter="jumpToPage" 
            min="1" 
            :max="totalPages"
            style="width: 50px; text-align: center;"
        />
        <span>of {{ totalPages }}</span>

        <button :disabled="currentPage >= totalPages" @click="getTasksCompleted(currentPage + 1)">Next</button>
    </div>
</template>
<script>

import axios from 'axios';
import moment from 'moment';
export default {
    name: 'AdminSupport',
    countPending: 0,

    created(){
        this.getTasksPending()
        /*this.getTechnicians()*/
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
            //For NAVBARS/////////////////////////////////////
            activeIndex: 0, // Default active index
            links: [
                { text: "PENDING", href: "#"},
                { text: "ALL", href: "#" },
                { text: "CANCELLED", href: "#" },
                { text: "COMPLETED", href: "#", disabled: false },
            ],
            //////////////////////////////////////////////////


            tasks: [],
            technicians: [],
            errorMsg: '',
            moment: moment,
            statusTech: true, 
            columns: columns,
            sortOrders: sortOrders,
            styleHeader: {
                backgroundColor: '#04AA6D',
            },
            showFindings: false,
            activeTaskId: null,

            currentPage: 1,
            inputPage: 1, // User-entered page number
            pageSize: 10, // Number of tasks per page
            totalTasks: 0, // Will be updated from API response
        };
    },
    computed: {
    totalPages() {
        return Math.ceil(this.totalTasks / this.pageSize);
    }
},
    methods:{
        toggleFindings(taskId) {
             // Toggle the visibility of the template for the clicked task
            this.activeTaskId = this.activeTaskId === taskId ? null : taskId;
        },
        //For NAVBARS/////////////////////////////////////
        setActive(index) {
            if (!this.links[index].disabled) {
                this.activeIndex = index;
                //alert(index);
                if(index === 0){
                    this.getTasksPending();
                }else if(index === 1){
                    //alert("pumasok sa ALL")
                    this.getTasks();
                }else if(index === 2){
                    //alert("pumasok sa CANCELLED")
                    this.getTasksCancelled();
                }else if(index === 3){
                    //alert("pumasok sa COMPLETED")
                    this.getTasksCompleted();
                }
            }
        },
        //////////////////////////////////////////////////

        getTasks(page = 1){
            this.showFindings = true;
            this.styleHeader.backgroundColor = '#04AA6D';
            axios.get('/api/admin-support', {
                params:{
                    page: page,
                    pageSize: this.pageSize,
                }
            })
             .then((response) => {
                console.log(response.data)
                this.tasks = response.data.support; // Ensure your API returns paginated tasks
                this.totalTasks = response.data.total; // Total tasks from API response
                this.currentPage = page;
                this.inputPage = page; // Sync input with current page
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },

        getTasksPending(page = 1){
            this.showFindings = true;
            this.styleHeader.backgroundColor = '#04AA6D';
            axios.get('/api/admin-support-pending', {
                params:{
                    page: page,
                    pageSize: this.pageSize,
                }
            })
             .then((response) => {
                console.log(response.data)
                this.tasks = response.data.support; // Ensure your API returns paginated tasks
                this.technicians = response.data.tech;
                this.totalTasks = response.data.total; // Total tasks from API response
                this.currentPage = page;
                this.inputPage = page; // Sync input with current page
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        getTasksCancelled(page = 1){
            this.showFindings = true;
            this.styleHeader.backgroundColor = '#04AA6D';
            axios.get('/api/admin-support-cancelled', {
                params:{
                    page: page,
                    pageSize: this.pageSize,
                }
            })
             .then((response) => {
                console.log(response.data)
                this.tasks = response.data.support; // Ensure your API returns paginated tasks
                this.totalTasks = response.data.total; // Total tasks from API response
                this.currentPage = page;
                this.inputPage = page; // Sync input with current page
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },
        getTasksCompleted(page = 1){
            this.showFindings = true;
            this.styleHeader.backgroundColor = '#04AA6D';
            axios.get('/api/admin-support-completed', {
                params:{
                    page: page,
                    pageSize: this.pageSize,
                }
            })
             .then((response) => {
                console.log(response.data)
                this.tasks = response.data.support; // Ensure your API returns paginated tasks
                this.totalTasks = response.data.total; // Total tasks from API response
                this.currentPage = page;
                this.inputPage = page; // Sync input with current page
             })
             .catch((error) => {
                console.log(error)
                this.errorMsg = error
             })
        },

        jumpToPage() {
            this.getTasks(this.inputPage);
        },
    }
}
</script>
<style scoped>
.nav-link {
  cursor: pointer;
}

/* Responsive Table */
.table-container {
    overflow-x: auto;
    width: 100%;
}

/* Mobile Optimization */
@media (max-width: 768px) {
    .table {
        display: block;
        width: 100%;
    }
    
    thead {
        display: none;
    }
    
    tbody tr {
        display: flex;
        flex-direction: column;
        border-bottom: 2px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
    }
    
    tbody tr td {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
    }
    
    tbody tr td::before {
        content: attr(data-label);
        font-weight: bold;
        flex: 1;
        padding-right: 10px;
    }
    
    button {
        width: 100%;
    }

    .mobile-bg {
        background-color: #008CBA !important; /* Apply white background only on mobile */
    }
    .mobile-hide {
        display: none !important; /* Apply white background only on mobile */
    }
    .mobile-show {
        display: inline !important; /* Apply white background only on mobile */
    }
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 10px;
}

.pagination button {
    padding: 8px 12px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    transition: 0.3s;
}

.pagination button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.pagination input {
    width: 50px;
    text-align: center;
    padding: 5px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
/* Responsive adjustments */
@media (max-width: 480px) {
    .pagination {
        flex-direction: column;
        gap: 6px;
    }

    .pagination button {
        width: 100%;
        font-size: 16px;
    }

    .pagination input {
        width: 60px;
        font-size: 16px;
    }
}
</style>