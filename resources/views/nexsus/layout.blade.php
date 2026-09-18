<!DOCTYPE html>
@include('layouts.lang')
<head>
   @stack('Nexsus Tracker-head')
   @stack('Nexsus Tracker-head-end')
</head>
<body>
   @stack('Nexsus Tracker-body-start')
   <div class="container">
      <div class="row">
         <div class="column" style="margin-top: 5%">
            @stack('Nexsus Tracker-content')
         </div>
      </div>
   </div>
   @stack('Nexsus Tracker-body-end')
</body>
</html>