const toggle=document.querySelector('.nav-toggle');
const links=document.querySelector('#navLinks');
if(toggle&&links){
  toggle.addEventListener('click',()=>{
    const open=links.classList.toggle('open');
    toggle.setAttribute('aria-expanded',String(open));
  });
  links.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{
    links.classList.remove('open');
    toggle.setAttribute('aria-expanded','false');
  }));
}

const lxTrack=document.querySelector('.lx-carousel-track');
const lxSlides=[...document.querySelectorAll('.lx-slide')];
const lxDots=[...document.querySelectorAll('.lx-carousel-dots button')];
const lxPrev=document.querySelector('.lx-prev');
const lxNext=document.querySelector('.lx-next');
let lxCurrent=0;
let lxTimer=null;
function lxShow(index){
  if(!lxTrack||!lxSlides.length) return;
  lxCurrent=(index+lxSlides.length)%lxSlides.length;
  lxTrack.style.transform=`translateX(-${lxCurrent*100}%)`;
  lxDots.forEach((dot,i)=>dot.classList.toggle('active',i===lxCurrent));
}
function lxStart(){
  if(lxTimer) clearInterval(lxTimer);
  lxTimer=setInterval(()=>lxShow(lxCurrent+1),6500);
}
lxDots.forEach((dot,i)=>dot.addEventListener('click',()=>{lxShow(i);lxStart();}));
lxPrev?.addEventListener('click',()=>{lxShow(lxCurrent-1);lxStart();});
lxNext?.addEventListener('click',()=>{lxShow(lxCurrent+1);lxStart();});
if(lxSlides.length){lxShow(0);lxStart();}
