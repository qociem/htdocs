const apiBase = 'api.php';
let activeProjectId = null;

// Helper
async function apiGet(path, params={}) {
  const url = new URL(apiBase + '?path=' + path, location.href);
  Object.keys(params).forEach(k => url.searchParams.append(k, params[k]));
  const r = await fetch(url);
  return r.json();
}
async function apiPostJSON(path, obj) {
  const r = await fetch(apiBase + '?path=' + path, {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify(obj)
  });
  return r.json();
}
async function apiPost(path, fd) {
  const r = await fetch(apiBase + '?path=' + path, {method:'POST', body:fd});
  return r.json();
}
async function apiDelete(path, obj) {
  const r = await fetch(apiBase + '?path=' + path, {
    method:'DELETE', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams(obj)
  });
  return r.json();
}
function escapeHtml(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

// === PROJECT ===
async function loadProjects(){
  const projects = await apiGet('projects');
  const list = document.getElementById('projectsList');
  list.innerHTML = '';
  projects.forEach(p=>{
    const div = document.createElement('div');
    div.className = 'project-item' + (p.id==activeProjectId?' active':'');
    div.innerHTML = `
      <div>
        <strong>${escapeHtml(p.name)}</strong>
        <div class="small">${escapeHtml(p.description||'')}</div>
      </div>
      <button class="btn danger" data-id="${p.id}">Hapus</button>`;
    div.addEventListener('click', ()=>{
      activeProjectId = p.id;
      document.getElementById('activeProjectName').textContent = p.name;
      document.querySelectorAll('.project-item').forEach(x=>x.classList.remove('active'));
      div.classList.add('active');
      document.getElementById('showAddNote').style.display='inline-block';
      loadNotes();
    });
    div.querySelector('button').addEventListener('click', async (e)=>{
      e.stopPropagation();
      if(!confirm('Hapus project ini?')) return;
      await apiDelete('projects',{id:p.id});
      if(p.id==activeProjectId){
        activeProjectId=null;
        document.getElementById('activeProjectName').textContent='(pilih project)';
        document.getElementById('showAddNote').style.display='none';
        resetNotesView();
      }
      loadProjects();
    });
    list.appendChild(div);
  });
}

// === tampil/sembunyi form project ===
document.getElementById('showAddProject').addEventListener('click',()=>{
  document.getElementById('projectForm').style.display='block';
});
document.getElementById('cancelProjectBtn').addEventListener('click',()=>{
  document.getElementById('projectForm').style.display='none';
  document.getElementById('projectName').value='';
  document.getElementById('projectDesc').value='';
});
document.getElementById('addProjectBtn').addEventListener('click',async()=>{
  const name=document.getElementById('projectName').value.trim();
  if(!name){alert('Nama project wajib diisi');return;}
  const desc=document.getElementById('projectDesc').value.trim();
  const res=await apiPostJSON('projects',{name,description:desc});
  if(res.success){loadProjects();}
  document.getElementById('projectForm').style.display='none';
  document.getElementById('projectName').value='';
  document.getElementById('projectDesc').value='';
});

// === NOTES ===
function resetNotesView(){
  const list=document.getElementById('notesList');
  list.innerHTML='<div class="empty-placeholder">Belum ada catatan untuk ditampilkan.</div>';
}

async function loadNotes(){
  const list=document.getElementById('notesList');
  if(!activeProjectId){ resetNotesView(); return; }
  const notes=await apiGet('notes',{project_id:activeProjectId});
  list.innerHTML='';
  if(!notes.length){
    list.innerHTML='<div class="empty-placeholder">Belum ada catatan untuk project ini.</div>';
    return;
  }
  notes.forEach(n=>{
    const card=document.createElement('div');
    card.className='note-card';
    let att='';
    if(n.attachment){
      const url='uploads/'+n.attachment;
      if((n.mime_type||'').startsWith('image'))
        att=`<div class="attachment"><img src="${url}" style="max-width:100%;border-radius:6px;"></div>`;
      else if((n.mime_type||'').startsWith('video'))
        att=`<div class="attachment"><video controls style="max-width:100%;border-radius:6px;"><source src="${url}"></video></div>`;
    }
    card.innerHTML=`<div style="display:flex;justify-content:space-between;">
      <strong>${escapeHtml(n.title)}</strong>
      <button class="btn danger" data-id="${n.id}">Hapus</button></div>
      <div class="small">${new Date(n.created_at).toLocaleString()}</div>
      <div style="margin-top:6px;">${escapeHtml(n.content||'')}</div>${att}`;
    card.querySelector('button').addEventListener('click',async()=>{
      if(!confirm('Hapus catatan ini?'))return;
      await apiDelete('notes',{id:n.id});
      loadNotes();
    });
    list.appendChild(card);
  });
}

// tampil/sembunyi form note
document.getElementById('showAddNote').addEventListener('click',()=>{
  document.getElementById('noteForm').style.display='block';
});
document.getElementById('cancelNoteBtn').addEventListener('click',()=>{
  document.getElementById('noteForm').style.display='none';
  document.getElementById('noteTitle').value='';
  document.getElementById('noteContent').value='';
  document.getElementById('noteAttachment').value='';
});
document.getElementById('addNoteBtn').addEventListener('click',async()=>{
  if(!activeProjectId){alert('Pilih project dulu');return;}
  const title=document.getElementById('noteTitle').value.trim();
  if(!title){alert('Judul catatan wajib diisi');return;}
  const content=document.getElementById('noteContent').value;
  const file=document.getElementById('noteAttachment').files[0];
  const fd=new FormData();
  fd.append('project_id',activeProjectId);
  fd.append('title',title);
  fd.append('content',content);
  if(file)fd.append('attachment',file);
  const res=await apiPost('notes',fd);
  if(res.success){loadNotes();}
  document.getElementById('noteForm').style.display='none';
  document.getElementById('noteTitle').value='';
  document.getElementById('noteContent').value='';
  document.getElementById('noteAttachment').value='';
});

// === INIT ===
window.addEventListener('load',()=>{
  loadProjects();
  resetNotesView();
});
