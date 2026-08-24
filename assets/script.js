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

const slides=[...document.querySelectorAll('.hero-slider .slide')];
const dots=[...document.querySelectorAll('.slider-dots button')];
const prev=document.querySelector('.slider-prev');
const next=document.querySelector('.slider-next');
let currentSlide=0;
let sliderTimer=null;
function showSlide(index){
  if(!slides.length) return;
  currentSlide=(index+slides.length)%slides.length;
  slides.forEach((slide,i)=>slide.classList.toggle('active',i===currentSlide));
  dots.forEach((dot,i)=>dot.classList.toggle('active',i===currentSlide));
}
function startSlider(){
  if(sliderTimer) clearInterval(sliderTimer);
  sliderTimer=setInterval(()=>showSlide(currentSlide+1),5500);
}
if(slides.length){
  dots.forEach((dot,i)=>dot.addEventListener('click',()=>{showSlide(i);startSlider();}));
  prev?.addEventListener('click',()=>{showSlide(currentSlide-1);startSlider();});
  next?.addEventListener('click',()=>{showSlide(currentSlide+1);startSlider();});
  showSlide(0);
  startSlider();
}
