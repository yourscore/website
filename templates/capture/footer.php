	</div>

	<!-- Footer -->
		<footer id="footer">
			<div class="in">
				<section class="copyright-area <?php echo ( is_active_sidebar( 'copyright-area-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
					<?php sds_copyright_area_sidebar(); ?>
				</section>
			</div>

			<section class="copyright">
				<div class="in">
					<p class="copyright-message">
						<?php sds_copyright( 'Capture' ); ?>
					</p>
				</div>
			</section>
		</footer>

		<?php wp_footer(); ?>
	</body>
</html>