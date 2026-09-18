<script>{!! file_get_contents(base_path("assets/js/jquery.min.js")) !!}</script>
<script>
    $(document).ready(function () {
      var submitBtn = $('#submit-btn');
      
      $('#handle').on('keyup', function () {
        var handle = $(this).val();
  
        if (handle.trim() !== '') {
          $.ajax({
            type: 'POST',
            url: '{{url("/validate-handle")}}',
            data: {
              '_token': '{{ csrf_token() }}',
              'handle': handle
            },
            success: function (data) {
              $('#handle').removeClass('is-valid is-invalid');
              $('#username-error').remove();
  
              if (typeof exceptionvar !== 'undefined' && handle.trim() === exceptionvar) {
                submitBtn.prop('disabled', false);
              } else {
                if (data.valid) {
                  $('#handle').addClass('is-valid');
                  submitBtn.prop('disabled', false);
                } else {
                  $('#handle').addClass('is-invalid');
                  $('<div id="username-error" class="invalid-feedback">That username is already taken</div>').insertAfter('#handle');
                  submitBtn.prop('disabled', true);
                }
              }
            }
          });
        } else {
          $('#handle').removeClass('is-valid is-invalid');
          $('#username-error').remove();
          submitBtn.prop('disabled', true);
        }
      });
    });
  </script>
