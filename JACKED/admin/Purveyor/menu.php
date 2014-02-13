<div class="container">
    <div id="menuitems" class="row">
        <div class="">
            <h2>Add Sale</h2>
            <p>Manually add a sale.</p>
            <a class="btn large" disabled="disabled" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor?manage_section=add_sale">Add</a>
        </div>

        <div class="">
            <h2>Sales</h2>
            <p>View and update sales.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor?manage_section=sales">Manage</a>
        </div>
        
        <div class="">
            <h2>Tickets</h2>
            <p>Manage and create tickets.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor?manage_section=tickets">Manage</a>
        </div>
        
        <div class="">
            <h2>Promotions</h2>
            <p>Manage and create Promotions for Tickets.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor?manage_section=promotions">Manage</a>
        </div>
        
        <div class="">
            <h2>Products</h2>
            <p>Manage and create Products.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor?manage_section=products">Manage</a>
        </div>
    </div>
</div>