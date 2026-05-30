const out = id => document.getElementById(id);
const output = out('output');

function apiCall(formData){
  return fetch('api.php', {method:'POST',body:formData}).then(r=>r.json());
}

document.getElementById('loginForm').addEventListener('submit', e=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  apiCall(fd).then(j=>output.textContent=JSON.stringify(j,null,2));
});

document.getElementById('verifyForm').addEventListener('submit', e=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  apiCall(fd).then(j=>output.textContent=JSON.stringify(j,null,2));
});

document.querySelectorAll('.actions button').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const a = btn.getAttribute('data-action');
    if(a==='transfer-form'){
      document.getElementById('transferForm').style.display='block';
      return;
    }
    const fd = new FormData();
    fd.append('action', a);
    apiCall(fd).then(j=>output.textContent=JSON.stringify(j,null,2));
  });
});

document.getElementById('transferForm').addEventListener('submit', e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  apiCall(fd).then(j=>output.textContent=JSON.stringify(j,null,2));
});
