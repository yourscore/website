<script type="text/template" id="capture-slideshow-template">
	<section class="capture-slideshow-photo capture-slideshow-image">
		<img src="<%= img.src %>" class="capture-slideshow-image" width="<%= img.width %>" height="<%= img.height %>" />
	</section>
	<section class="capture-slideshow-details capture-slideshow-ui">
		<section class="capture-slideshow-post-details">
			<h1 class="post-title"><%= post.title %></h1>
			<p class="post-date"><%= post.date %></p>
		</section>
	</section>
	<input type="hidden" class="capture-slideshow-current-post-id" value="<%= post.id %>" />
</script>