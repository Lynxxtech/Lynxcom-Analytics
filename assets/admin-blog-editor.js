document.querySelectorAll('.rich-post-form').forEach((form)=>{
  const editorEl=form.querySelector('.wysiwyg-editor');
  const hidden=form.querySelector('.hidden-html');
  const plain=form.querySelector('.plain-content');
  if(!editorEl||!hidden) return;

  if(window.Quill){
    const initialHTML=editorEl.innerHTML.trim();
    const quill=new Quill(editorEl,{
      theme:'snow',
      placeholder:editorEl.dataset.placeholder||'Write your blog post...',
      modules:{
        toolbar:[
          [{header:[2,3,false]}],
          ['bold','italic','underline'],
          [{list:'ordered'},{list:'bullet'}],
          [{align:[]}],
          ['blockquote','link','image'],
          ['clean']
        ]
      }
    });
    if(initialHTML){ quill.root.innerHTML=initialHTML; }
    form.addEventListener('submit',()=>{
      hidden.value=quill.root.innerHTML.trim();
      if(plain && !plain.value.trim()) plain.value=quill.getText().trim();
    });
    return;
  }

  // Fallback if CDN is blocked: still save editable HTML.
  editorEl.setAttribute('contenteditable','true');
  form.addEventListener('submit',()=>{
    hidden.value=editorEl.innerHTML.trim();
    if(plain && !plain.value.trim()) plain.value=editorEl.innerText.trim();
  });
});
