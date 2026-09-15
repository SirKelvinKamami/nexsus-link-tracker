@php
$gtmId = env('GTM_ID');
$ga4Id = env('GA4_ID');
@endphp

@if($gtmId || $ga4Id)
@push('linkstack-head')
<!-- Nexsus Analytics -->
<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}</script>
@if($gtmId)
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif
@if($ga4Id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
<script>gtag('js', new Date()); gtag('config', '{{ $ga4Id }}');</script>
@endif
@endpush

@push('linkstack-body-start')
@if($gtmId)
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
<script>
(function(){
  if(typeof window.dataLayer === 'undefined') return;
  document.addEventListener('click', function(e){
    var a = e.target.closest('a');
    if(!a) return;
    var href = (a.getAttribute('href')||'');
    var isGoing = href.indexOf('/going/') !== -1;
    window.dataLayer.push({
      event: 'outbound_link_click',
      link_url: a.href || '',
      link_id: isGoing ? (href.split('/going/')[1]||'').replace(/[^0-9]/g,'') : '',
      link_title: (a.getAttribute('title') || a.textContent || '').trim().substring(0,100)
    });
  });
})();
</script>
@endpush
@endif