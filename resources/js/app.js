import './bootstrap';
import {createApp} from 'vue'
import moment from "moment";

//import App from './App.vue'
import Greet from './Greet.vue'
import Ticket from './Ticket.vue'
import Update from './Update.vue'
import Create from './Create.vue'
import Remove from './Remove.vue'
import Survey from './tech/Survey.vue'
import Report from './Report.vue'
import AdminSupport from './AdminSupport.vue'

//createApp(App).mount("#app")

createApp(Greet).mount("#greet") 
createApp(Ticket).mount("#ticket")
createApp(Update).mount("#update")
createApp(Create).mount("#create")
createApp(Remove).mount("#remove")
createApp(Survey).mount("#survey")
createApp(Report).mount("#report")
createApp(AdminSupport).mount("#admin-support")
window.moment = moment();