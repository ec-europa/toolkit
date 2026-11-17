<?php
/**
 * @file
 * Default theme implementation to display a block.
 *
 * Available variables:
 * - $block->subject: Block title.
 * - $content: Block content.
 * - $block->module: Module that generated the block.
 * - $block->delta: An ID for the block, unique within each module.
 * - $block->region: The block region embedding the current block.
 * - $classes: String of classes that can be used to style contextually through
 *   CSS. It can be manipulated through the variable $classes_array from
 *   preprocess functions. The default values can be one or more of the
 *   following:
 *   - block: The current template type, i.e., "theming hook".
 *   - block-[module]: The module generating the block. For example, the user
 *     module is responsible for handling the default user navigation block. In
 *     that case the class would be 'block-user'.
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 *
 * Helper variables:
 * - $classes_array: Array of html class attribute values. It is flattened
 *   into a string within the variable $classes.
 * - $block_zebra: Outputs 'odd' and 'even' dependent on each block region.
 * - $zebra: Same output as $block_zebra but independent of any block region.
 * - $block_id: Counter dependent on each block region.
 * - $id: Same output as $block_id but independent of any block region.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 * - $is_admin: Flags true when the current user is an administrator.
 * - $block_html_id: A valid HTML ID and guaranteed unique.
 *
 * @see template_preprocess()
 * @see template_preprocess_block()
 * @see template_process()
 *
 * @ingroup themeable
 */
?>
<div id="<?php print $block_html_id; ?>" class="<?php print $classes; ?> <?php print ($panel ? 'panel panel-default clearfix' : ''); ?>">

  <?php print render($title_prefix); ?>
  <?php if ($title && $block->subject): ?>
    <div class="<?php print ($panel ? 'panel-heading' : ''); ?>">
      <?php print $block->subject ?>
    </div>
  <?php endif;?>
  <?php print render($title_suffix); ?>
  <a href='sites/all/themes/bartik/logo.png'>Hardcoded link to theme</a>
  <a href='sites/all/modules/bartik/logo.png'>Hardcoded link to theme</a>
  <a href='sites/my-cool-site/themes/bartik/logo.png'>Hardcoded link to theme</a>
  <a href='sites/site2/themes/bartik/logo.png'>Hardcoded link to theme</a>
  <a href='sites/' . $variable . '/themes/bartik/logo.png'>Hardcoded link to theme</a>
  <a href="sites/" . $variable . "/files/bartik/logo.png">Hardcoded link to files</a>


  sites\/[a-zA-Z-0-9\$\.\ \"]+\/(modules|themes|libraries)*
  <p>This is a warning-free testing file.</p>
  <div class="<?php print ($panel && $body_class ? 'panel-body' : ''); ?> content"<?php print $content_attributes; ?>>
    <img width="270" height="104" onload="google.aft&amp;&amp;google.aft(this)" alt="Image result for google" class="irc_mut iHKKh9bmcfLU-HwpH6ZlgJaI" src="data:image/png;base64,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" style="margin-top: 209px;">
  </div>

</div>