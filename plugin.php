<?php

ET::$pluginInfo["Featured"] = array(
    "name"        => "Featured",
    "description" => "A plugin enabling the label of featured posts",
    "version"     => "1.0",
    "author"      => "Z. Tong Zhang",
    "authorEmail" => "zhang@zhantong.org",
    "authorURL"   => "https://ztong.me",
    "license"     => "GPLv2"
);

class ETPlugin_Featured extends ETPlugin {
    public function init() {
        ET::define("label.featured", "Featured");

        ET::conversationModel();
        ETConversationModel::addLabel("featured", "IF(c.featured = 1, 1, 0)", "icon-star");
    }

    public function setup($oldVersion = "") {
        $structure = ET::$database->structure();
        $structure->table("conversation")
        ->column("featured", "bool", 0)
        ->exec(false);
        return true;
    }

    public function handler_renderBefore($sender)
    {
        $sender->addCSSFile($this->resource("featured.css"));
    }

    public function handler_conversationController_conversationIndexDefault($sender, $conversation, $controls, $replyForm, $replyControls)
    {
        if ($conversation["canModerate"]) {
            $controls->add("featured", "<a href='".URL("conversation/featured/".$conversation["conversationId"]."/?token=".ET::$session->token."&return=".urlencode($sender->selfURL))."' id='control-featured'><i class='icon-star'></i> <span>".T($conversation["featured"] ? "Un-feature it" : "Feature it")."</span></a>", 0);
        }
    }

    public function handler_conversationController_renderBefore($sender)
    {
        $sender->addJSFile($this->resource("featured.js"));
    }

    public function action_conversationController_featured($controller, $conversationId = false)
    {
        if (!$controller->validateToken()) return;

        // Get the conversation.
        if (!($conversation = $controller->getConversation($conversationId))) return;

        if (!ET::$session->isAdmin() and !$conversation["canModerate"]) return;

        $featured = !$conversation["featured"];
        $this->setFeatured($conversation, $featured);

        $controller->json("featured", $featured);

        if ($controller->responseType === RESPONSE_TYPE_DEFAULT) {
            redirect(URL(R("return", conversationURL($conversation["conversationId"], $conversation["title"]))));
        }

        elseif ($controller->responseType === RESPONSE_TYPE_AJAX)
        $controller->json("labels", $controller->getViewContents("conversation/labels", array("labels" => $conversation["labels"])));

        $controller->render();
    }

    public function setFeatured(&$conversation, $featured)
    {
        $featured = (bool)$featured;

        $model = ET::conversationModel();
        $model->updateById($conversation["conversationId"], array("featured" => $featured));

        $model->addOrRemoveLabel($conversation, "featured", $featured);
        $conversation["featured"] = $featured;
    }

}

?>
