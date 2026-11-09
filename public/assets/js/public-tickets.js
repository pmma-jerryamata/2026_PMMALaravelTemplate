const publicTimeline=document.getElementById('public-timeline-dialog');
document.querySelectorAll('[data-close-public-timeline]').forEach(button=>button.addEventListener('click',()=>publicTimeline.close()));
document.addEventListener('click',async event=>{
    const button=event.target.closest('[data-public-timeline-url]');if(!button)return;
    const content=document.getElementById('public-timeline-content');content.textContent='Loading timeline…';publicTimeline.showModal();
    try{
        const response=await fetch(button.dataset.publicTimelineUrl,{headers:{Accept:'application/json'}});
        if(!response.ok)throw new Error('Unable to load timeline. Please try again.');
        const data=await response.json();document.getElementById('public-timeline-title').textContent=data.reference+' — Timeline';content.innerHTML=data.html;
    }catch(error){content.textContent=error.message;}
});
