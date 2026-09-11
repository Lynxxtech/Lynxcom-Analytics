
const toggle=document.querySelector('.nav-toggle');
const links=document.querySelector('#navLinks');
if(toggle&&links){toggle.addEventListener('click',()=>links.classList.toggle('open'));}
document.querySelectorAll('.nav-links a').forEach(a=>a.addEventListener('click',()=>links?.classList.remove('open')));
