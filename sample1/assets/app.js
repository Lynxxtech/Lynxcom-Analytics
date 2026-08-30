const money=n=>'₦'+Number(n||0).toLocaleString('en-NG',{maximumFractionDigits:0});
const shortMoney=n=>'₦'+(Number(n||0)/1000000).toFixed(1)+'M';
const colors=['#1455d9','#14b8d4','#f8c14a','#079455','#d92d20','#7c3aed','#0f766e','#f97316'];
let DATA, debtors=[];
fetch('data/dashboard-data.json').then(r=>r.json()).then(data=>{DATA=data; debtors=data.debtors; render();});
function chart(id,type,labels,datasets,options={}){return new Chart(document.getElementById(id),{type,data:{labels,datasets},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},...options}})}
function render(){
 document.getElementById('schoolName').textContent=DATA.school.name;
 const k=DATA.kpis;
 const cards=[['Active Students',k.active_students,'current active records'],['Primary Students',k.primary_students,'day section'],['Boarders',k.secondary_boarders,'secondary boarding'],['Fee Collection',k.collection_rate+'%','paid vs billed'],['Outstanding Fees',shortMoney(k.outstanding_fees),'to follow up'],['Parent Debtors',k.debtors_count,'action list']];
 document.getElementById('kpis').innerHTML=cards.map(c=>`<article class="kpi-card"><span>${c[0]}</span><strong>${c[1]}</strong><small>${c[2]}</small></article>`).join('');
 chart('enrollmentChart','bar',DATA.charts.enrollment_by_class.map(x=>x.label.replace('Primary ','P').replace('JSS ','J').replace('SS ','S')),[{label:'Students',data:DATA.charts.enrollment_by_class.map(x=>x.value),backgroundColor:DATA.charts.enrollment_by_class.map(x=>x.label.startsWith('Primary')?'#1455d9':'#14b8d4'),borderRadius:8}]);
 chart('feeStatusChart','bar',['Billed','Collected','Outstanding'],[{label:'Amount',data:[k.fees_billed,k.fees_collected,k.outstanding_fees],backgroundColor:['#08275c','#079455','#d92d20'],borderRadius:10}],{indexAxis:'y'});
 chart('feeClassChart','bar',DATA.charts.fee_by_class.map(x=>x.class.replace('Primary ','P').replace('JSS ','J').replace('SS ','S')),[{label:'Paid',data:DATA.charts.fee_by_class.map(x=>x.paid),backgroundColor:'#079455',borderRadius:6},{label:'Outstanding',data:DATA.charts.fee_by_class.map(x=>x.outstanding),backgroundColor:'#d92d20',borderRadius:6}],{scales:{y:{ticks:{callback:v=>shortMoney(v)}}}});
 chart('hostelChart','doughnut',DATA.charts.hostels.map(x=>x.label),[{data:DATA.charts.hostels.map(x=>x.value),backgroundColor:colors}]);
 chart('admissionChart','bar',DATA.charts.admissions_stage.map(x=>x.label),[{label:'Leads',data:DATA.charts.admissions_stage.map(x=>x.value),backgroundColor:'#14b8d4',borderRadius:7}],{indexAxis:'y'});
 chart('expenseChart','pie',DATA.charts.expense_categories.slice(0,6).map(x=>x.label),[{data:DATA.charts.expense_categories.slice(0,6).map(x=>x.value),backgroundColor:colors}]);
 const classFilter=document.getElementById('classFilter');
 [...new Set(debtors.map(d=>d.class))].sort().forEach(c=>classFilter.insertAdjacentHTML('beforeend',`<option>${c}</option>`));
 ['debtorSearch','classFilter','priorityFilter'].forEach(id=>document.getElementById(id).addEventListener('input',renderDebtors));
 document.getElementById('updatePlan').innerHTML=DATA.update_plan.map(x=>`<li>${x}</li>`).join('');
 renderDebtors();
}
function renderDebtors(){
 const q=document.getElementById('debtorSearch').value.toLowerCase();
 const cls=document.getElementById('classFilter').value;
 const pri=document.getElementById('priorityFilter').value;
 const rows=debtors.filter(d=>(!cls||d.class===cls)&&(!pri||d.priority===pri)&&(!q||(`${d.student_name} ${d.guardian_name} ${d.guardian_phone} ${d.class}`).toLowerCase().includes(q))).slice(0,80);
 document.getElementById('debtorsBody').innerHTML=rows.map(d=>`<tr><td><strong>${d.student_name}</strong><br><small>${d.student_id}</small></td><td>${d.class}</td><td>${d.guardian_name}</td><td>${d.guardian_phone}</td><td class="money">${money(d.outstanding_balance)}</td><td><span class="pill ${d.priority}">${d.priority}</span></td><td>${d.recommended_action}</td></tr>`).join('');
}