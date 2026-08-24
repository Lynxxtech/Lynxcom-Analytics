document.querySelectorAll('.rich-post-form').forEach((form)=>{
  const editor=form.querySelector('.rich-editor');
  const hidden=form.querySelector('.hidden-html');
  const plain=form.querySelector('.plain-content');
  if(!editor||!hidden) return;
  form.querySelectorAll('.editor-toolbar button').forEach((btn)=>{
    btn.addEventListener('click',()=>{
      editor.focus();
      if(btn.dataset.image){
        const url=prompt('Paste image URL or path, e.g. assets/section-process.jpg');
        if(url){ document.execCommand('insertHTML',false,`<figure><img src="${url.replace(/"/g,'&quot;')}" alt="Blog image"><figcaption>Image caption</figcaption></figure>`); }
        return;
      }
      const cmd=btn.dataset.cmd;
      let value=btn.dataset.value||null;
      if(btn.dataset.prompt){ value=prompt(btn.dataset.prompt)||''; if(!value) return; }
      document.execCommand(cmd,false,value);
    });
  });
  form.addEventListener('submit',()=>{
    hidden.value=editor.innerHTML.trim();
    if(plain && !plain.value.trim()) plain.value=editor.innerText.trim();
  });
});