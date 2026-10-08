jQuery('a').click(function(e) {
  let href = jQuery(this).attr('href');
  if (href.endsWith('.pdf')) {
    let segments = href.split();
    gtag('event', 'page_view', {
      page_title: segments[segments.length - 1],
      page_location: href
    });
  }
});
