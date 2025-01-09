<?php
/**
 * Register system hook function call. 
 * This is added in the config file
 * @param string $hookPoint The hook point to call
 * @param integer $priority The priority for the given hook function
 * @param string|function Function name to call or anonymous function.
 *
 * @return Depends on hook function point.
 */
/*addHook('Page::edit', 1, function ($id = 0) { //use ($next_actions) does not work as it is early binding
    //echo "<pre>Hook Page::edit triggered";
    //print_r($next_actions);
    //echo "</pre>";
});

//This works too
//addHook('Page::edited', 1, 'Collection::getCollectionsByPageId');
/* Use this in case we need to have params processed, otherwise just use closure 
addHook('Page::edit', 1, [
	'target' => function ($vars) {
	    echo "<pre>Closure defined via next_actions";
	    print_r($vars); 
	    echo "</pre>";
	},
	'params' => ["Menu::getMenus" => []],
	'name'   => 'anonymous',
]);

addHook('Page::edited', 1, function ($vars) {
    //echo "<pre>Hook Page::edit'ED triggered";
    //print_r($next_actions);
    //echo "</pre>";
});
*/
?>
