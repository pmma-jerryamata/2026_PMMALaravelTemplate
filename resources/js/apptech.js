import './bootstrap';
import {createApp} from 'vue'
import moment from "moment";

import TechTicket from './tech/Ticket.vue'
import TechAdd from './tech/Add.vue'
import TechCompleted from './tech/Completed.vue'


//createApp(TechAdd).mount("#techadd")
createApp(TechCompleted).mount("#techcompleted")
window.moment = moment();

//This is to passby-value Auth::user()->id to Add.vue
const el = document.querySelector("#techadd");
if (el) {
    const techId = Number(el.getAttribute("tech-id")); // Convert to number

    createApp(TechAdd, { techId }).mount("#techadd");
    createApp(TechTicket, { techId }).mount("#techticket");
}

