function addTask(){
  const task =document.getElementById('taskInput');
  const listTask=document.getElementById('taskList');
  if(task.value!=""){
    
  const li=document.createElement('li');
  li.textContent=task.value;
  listTask.appendChild(li);
  task.value='';
  }
}