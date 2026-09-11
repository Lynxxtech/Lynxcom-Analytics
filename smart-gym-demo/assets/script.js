
const toggle=document.querySelector('.nav-toggle');
const links=document.querySelector('#navLinks');
if(toggle&&links){toggle.addEventListener('click',()=>links.classList.toggle('open'));}
document.querySelectorAll('.nav-links a').forEach(a=>a.addEventListener('click',()=>links?.classList.remove('open')));


// Interactive Smart Gym metric cards
const metricBoard=document.querySelector('.dynamic-metrics');
if(metricBoard){
  const insight=document.createElement('div');
  insight.className='metric-insight';
  metricBoard.appendChild(insight);
  metricBoard.querySelectorAll('.metric-tile').forEach(tile=>{
    tile.addEventListener('click',()=>{
      metricBoard.querySelectorAll('.metric-tile').forEach(t=>t.classList.remove('active'));
      tile.classList.add('active');
      insight.innerHTML=`<h3>${tile.dataset.label}: ${tile.dataset.value}</h3><p>${tile.dataset.detail}</p>`;
      insight.classList.add('show');
    });
  });
}
