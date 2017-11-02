<div class="pm_batch_add_to_package batch-action">

    <span style="opacity:0.35">Add to package : </span>
    <select class="batch-action pm_target_package" name="" disabled="disabled">
        <option value="-1" selected="true" disabled="disabled">Select a package</option>
        <?php
        foreach ($packagesNames as $id=>$name){
            $displayName = $name;
            if(strlen($displayName)>$NAME_MAX_LENGTH){
                $displayName = substr($displayName, 0, $NAME_MAX_LENGTH).'...';
            }
        ?>
           <option value="<?php echo $id ?>" title="<?php echo htmlspecialchars($name) ?>"><?php echo $id ?> : <?php echo $displayName ?></option>
        <?php } ?>
    </select>
    <input type="submit" class="small batch-action button" name="submit-batch-add-to-package" value="OK">
</div>


