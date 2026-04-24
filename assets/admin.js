(function ($) {
	"use strict";

	$(document).on("click", ".afca-upload-image", function (e) {
		e.preventDefault();
		var $btn = $(this);
		var targetId = $btn.data("target");
		var $target = $("#" + targetId);
		var $preview = $btn.closest(".afca-image-picker, .form-field, td, p").find(".afca-image-preview").first();

		var frame = wp.media({
			title: (window.AFCA_SEO_I18N && AFCA_SEO_I18N.pickTitle) || "Choose image",
			button: { text: (window.AFCA_SEO_I18N && AFCA_SEO_I18N.pickButton) || "Use this image" },
			multiple: false,
		});

		frame.on("select", function () {
			var att = frame.state().get("selection").first().toJSON();
			$target.val(att.url);
			$preview.html('<img src="' + att.url + '" alt="">');
		});

		frame.open();
	});

	$(document).on("click", ".afca-remove-image", function (e) {
		e.preventDefault();
		var $btn = $(this);
		var targetId = $btn.data("target");
		var $target = $("#" + targetId);
		var $preview = $btn.closest(".afca-image-picker, .form-field, td, p").find(".afca-image-preview").first();
		$target.val("");
		$preview.empty();
	});

	function updateCount($el) {
		var len = ($el.val() || "").length;
		var rec = parseInt($el.data("recommended"), 10) || 0;
		var $counter = $el.siblings(".afca-char-count").first();
		if (!$counter.length) {
			return;
		}

		var color = "inherit";
		if (rec) {
			if (len > rec) color = "#d63638";
			else if (len > rec * 0.85) color = "#dba617";
			else if (len > 0) color = "#2271b1";
		}
		var label = (window.AFCA_SEO_I18N && AFCA_SEO_I18N.chars) || "characters";
		var text = len + (rec ? " / " + rec : "") + " " + label;
		$counter.text(text).css("color", color);
	}

	$(document).on("input keyup change", ".afca-count", function () {
		updateCount($(this));
	});

	$(function () {
		$(".afca-count").each(function () {
			updateCount($(this));
		});
	});
})(jQuery);