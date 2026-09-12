
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

// Smart Shape mobile menu
(function(){
  const btn = document.querySelector('.mobile-menu-toggle');
  const nav = document.querySelector('.sg-header nav');
  if(!btn || !nav) return;
  btn.addEventListener('click', () => {
    const open = document.body.classList.toggle('mobile-menu-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.textContent = open ? 'Close' : 'Menu';
  });
  nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    document.body.classList.remove('mobile-menu-open');
    btn.setAttribute('aria-expanded','false');
    btn.textContent='Menu';
  }));
})();
