/*
  --- menu level scope settins structure --- 
  note that this structure has changed its format since previous version.

  adjust block_top by adding the height of any header a single <TR> in a file header.html in the root or style directory

*/

var output_div='menu-bar';
var menu_horiz = [
{
	'height': 20,
	'width': 115,
	'block_top': 0,
	'block_left': 130,
	'top': 0,
	'left': 125,
	'hide_delay': 300,
	'expd_delay': 150,
	'css' : {
		'outer': ['menu-color1', 'menu-color2'],
		'inner': ['menu-color1', 'menu-color2']
	}
},
{
	'height': 20,
	'width': 115,
	'block_top': 20,
	'block_left': 0,
	'top': 20,
	'left': 0,
	'css': {
		'outer' : ['menu-color1', 'menu-color2'],
		'inner' : ['menu-color1', 'menu-color2']
	}
},
{
	'block_top': 0,
	'block_left': 115,
	'css': {
		'outer' : ['menu-color1', 'menu-color2'],
		'inner' : ['menu-color1', 'menu-color2']
	}
}
];

var menu_vert = [
{
	'height': 20,
	'width': 115,
	'block_top': 20,
	'block_left': 0,
	'top': 20,
	'left': 0,
	'hide_delay': 300,
	'expd_delay': 150,
	'css' : {
		'outer': ['menu-color1', 'menu-color2'],
		'inner': ['menu-color1', 'menu-color2']
	}
},
{
	'height': 20,
	'width': 115,
	'block_top': 0,
	'block_left': 115,
	'top': 20,
	'left': 0,
	'css': {
		'outer' : ['menu-color1', 'menu-color2'],
		'inner' : ['menu-color1', 'menu-color2']
	}
},
{
	'block_top': 0,
	'block_left': 115,
	'css': {
		'outer' : ['menu-color1', 'menu-color2'],
		'inner' : ['menu-color1', 'menu-color2']
	}
}
]


