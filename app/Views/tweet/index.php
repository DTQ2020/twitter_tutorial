<?php echo form_open("tweet/auth_twitter"); ?>
<input type="submit" value="Authorise Twitter Account">
<?php echo form_close(); ?>

<hr>
<p>Make A Tweet</p>
<?php echo form_open("tweet/make_tweet"); ?>
Tweet: <input type="text" name="tweet"> <input type="submit" value="Tweet">
<?php echo form_close(); ?>