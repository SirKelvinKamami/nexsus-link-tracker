<!DOCTYPE html>
@include('layouts.lang')
<head>
   @stack('nexsus-head')
   @stack('nexsus-head-end')
</head>
<body>
   @stack('nexsus-body-start')
   <div class="container">
      <div class="row">
         <div class="column" style="margin-top: 5%">
            @stack('nexsus-content')
         </div>
      </div>
   </div>
   @stack('nexsus-body-end')
</body>
</html>