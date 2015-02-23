$(function() {

	ETConversation.toggleFeatured = function() {
		$("#control-featured span").html(T($("#control-featured span").html() == T("Feature it") ? T("Un-feature it") : T("Feature it")));
		$.ETAjax({
			url: "conversation/featured.ajax/" + ETConversation.id,
			success: function(data) {
				$("#conversationHeader .labels").html(data.labels);
			}
		});
	};

	$("#control-featured").click(function(e) {
		e.preventDefault();
		ETConversation.toggleFeatured();
	});

});
