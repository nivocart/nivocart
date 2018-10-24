<?php if ($comments) { ?>
  <?php foreach ($comments as $comment) { ?>
    <div class="article-reply">
      <div class="author"><b><?php echo $comment['author']; ?></b></div>
      <div class="comment-date"> <?php echo $comment['date_added']; ?></div>
      <div class="text"><?php echo $comment['comment']; ?></div>
      <br />
      <div><a onclick="setArticleId(<?php echo $comment['blog_comment_id']; ?>)"><?php echo $text_reply_comment; ?></a></div>
      <br />
      <?php if ($comment['comment_reply']) { ?>
        <?php foreach ($comment['comment_reply'] as $comment_reply) { ?>
          <div class="article-reply">
            <div class="author"><b><?php echo ucwords($comment_reply['author']); ?></b></div>
            <div class="comment-date"><?php echo date($this->language->get('text_date_format_long'), strtotime($comment_reply['date_added'])); ?></div>
            <div class="text"><?php echo $comment_reply['comment']; ?></div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  <?php } ?>
  <div class="pagination"><?php echo $pagination; ?></div>
<?php } else { ?>
  <div class="content"><?php echo $text_no_comment; ?></div>
<?php } ?>

<script type="text/javascript"><!--
function setArticleId(article_id) {
	$("#blog-reply-id").val(article_id);
	$("#reply-remove").css('display', 'inline');
}
//--></script>