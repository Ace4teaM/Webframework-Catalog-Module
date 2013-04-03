/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2013 Thomas AUGUEY <contact@aceteam.org>
    ---------------------------------------------------------------------------------------------------------------------------------------
    This file is part of WebFrameWork.

    WebFrameWork is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebFrameWork is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebFrameWork.  If not, see <http://www.gnu.org/licenses/>.
    ---------------------------------------------------------------------------------------------------------------------------------------
*/

//loading functions
//ajoutez à ce global les fonctions d'initialisations
Ext.define('MyApp.App', {});

/*------------------------------------------------------------------------------------------------------------------*/
/**
 * @brief Initialise le viewport
 * @remarks convertie le HTML existant en layout dynamique
 * */
/*------------------------------------------------------------------------------------------------------------------*/

Ext.apply(MyApp.Loading, {onInitLayout:function(Y){

    var wfw = Y.namespace("wfw");
    var g = MyApp.global.Vars;

    // Nord
    g.statusPanel = Ext.create('Ext.Panel', {
        header:false,
        layout: 'hbox',
        region: 'north',     // position for region
        split: true,         // enable resizing
        margins: '0 5 5 5',
        /*html: Y.Node.one("#menu").get("innerHTML")*/
        items: [{
            header:false,
            border: false,
            width:200,
            contentEl: Y.Node.one("#header").getDOMNode()
        },{
            header:false,
            width:"100%",
            border: false,
            contentEl: Y.Node.one("#status").getDOMNode()
        }],
        renderTo: Ext.getBody()
    });

    // Centre
    g.contentPanel = Ext.create('Ext.Panel', {
        header :false,
        //title: 'Content',
        region: 'center',     // position for region
        height: 100,
        split: true,         // enable resizing
        margins: '0',
        layout: 'vbox',
        autoScroll:true,
        //html: Y.Node.one("#content").get("innerHTML")
        defaults:{
            header:false,
            border: false,
            width:"100%"
        },
        items: [
            { contentEl: Y.Node.one("#result").getDOMNode() },
            { contentEl: Y.Node.one("#content").getDOMNode() }
        ],
        renderTo: Ext.getBody()
    });

    // Ouest
    g.menuPanel = Ext.create('Ext.Panel', {
        title: 'Menu',
        layout: {
            // layout-specific configs go here
            type: 'accordion',
            titleCollapse: false,
            animate: true,
            activeOnTop: true
        },
        region: 'west',     // position for region
        width: 200,
        split: true,         // enable resizing
        margins: '0 5 5 5',
        /*html: Y.Node.one("#menu").get("innerHTML")*/
        items: [{
            title: 'Administrateur',
            contentEl: Y.Node.one("#menu1").getDOMNode()
        },{
            title: 'Visiteur',
            contentEl: Y.Node.one("#menu2").getDOMNode()
        },{
            title: 'Utilisateur',
            contentEl: Y.Node.one("#menu3").getDOMNode()
        }],
        renderTo: Ext.getBody()
    });

    // Sud
    g.footerPanel = Ext.create('Ext.Panel', {
        header :false,
        //title: 'Pied de page',
        region: 'south',     // position for region
        split: true,         // enable resizing
        margins: '0 5 5 5',
        contentEl: Y.Node.one("#footer").getDOMNode()
    });

    //viewport
    g.viewport = Ext.create('Ext.Viewport', {
        layout: 'border',
        items: [g.contentPanel,g.menuPanel,g.statusPanel,g.footerPanel]
    });
}});
