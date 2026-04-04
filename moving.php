<?php
require 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Moving</title>

<style>
body {
  font-family:Arial;
  background:#f4f6f9;
  margin:0;
  padding:40px;
}

.container {
  max-width:1000px;
  margin:auto;
  display:flex;
  gap:30px;
}

.box {
  flex:1;
  background:white;
  padding:25px;
  border-radius:15px;
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.calendar-grid {
  display:grid;
  grid-template-columns:repeat(7,1fr);
  gap:8px;
  margin-top:15px;
}

.day {
  padding:10px;
  background:#eee;
  text-align:center;
  border-radius:8px;
  cursor:pointer;
}

.day.selected {
  background:#2563eb;
  color:white;
}

.time-option {
  display:block;
  padding:10px;
  margin:8px 0;
  background:#eee;
  border-radius:8px;
  cursor:pointer;
}

.time-option.selected {
  background:#2563eb;
  color:white;
}

.confirm-btn {
  margin-top:20px;
  padding:12px;
  width:100%;
  background:#2563eb;
  color:white;
  border:none;
  border-radius:8px;
  cursor:pointer;
}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">

  <div class="box">
    <h3>Select Date</h3>
    <div id="calendar" class="calendar-grid"></div>
  </div>

  <div class="box">
    <h3>Select Time</h3>
    <div id="times"></div>
  </div>

  <div class="box">
    <h3>Confirmation</h3>
    <button class="confirm-btn" id="confirmBtn">Confirm Appointment</button>
    <p id="confirmation"></p>
  </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

  const calendar = document.getElementById("calendar");
  const timesContainer = document.getElementById("times");
  const confirmation = document.getElementById("confirmation");

  let selectedDate=null;
  let selectedTime=null;

  for(let i=1;i<=30;i++){
    const day=document.createElement("div");
    day.className="day";
    day.innerText=i;

    day.onclick=function(){
      document.querySelectorAll(".day").forEach(d=>d.classList.remove("selected"));
      day.classList.add("selected");
      selectedDate=i;
    };

    calendar.appendChild(day);
  }

  const times=["9AM","10AM","11AM","12PM","1PM","2PM","3PM","4PM"];

  times.forEach(time=>{
    const div=document.createElement("div");
    div.className="time-option";
    div.innerText=time;

    div.onclick=function(){
      document.querySelectorAll(".time-option").forEach(t=>t.classList.remove("selected"));
      div.classList.add("selected");
      selectedTime=time;
    };

    timesContainer.appendChild(div);
  });

  document.getElementById("confirmBtn").onclick=function(){
    if(selectedDate && selectedTime){
      confirmation.innerHTML=
      `✅ Moving scheduled for day ${selectedDate} at ${selectedTime}`;
    } else {
      confirmation.innerHTML=
      `⚠ Please select date and time.`;
    }
  };

});
</script>
<?php include 'footer.php'; ?>
</body>
</html>
