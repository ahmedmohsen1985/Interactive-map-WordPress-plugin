(function() {
    'use strict';

    window.dimInitMap = function() {
        if ( ! window.dimMapData || ! window.google ) {
            return;
        }

        defineDimClasses();

        for ( let i = 0; i < window.dimMapData.length; i++ ) {
            const config = window.dimMapData[i];
            try {
                config.styles = config.styleJson ? JSON.parse( config.styleJson ) : [];
            } catch ( e ) {
                config.styles = [];
            }
            new window.DimMap( config );
        }

        window.dimMapData = [];
    };

    function defineDimClasses() {
        if ( window.__dimClassesDefined ) {
            return;
        }
        window.__dimClassesDefined = true;

        window.DimMap = function( config ) {
            this.uid = config.uid;
            this.canvas = document.getElementById( this.uid + '-canvas' );
            if ( ! this.canvas ) {
                return;
            }

            this.map = new google.maps.Map( this.canvas, {
                center: { lat: config.center.lat, lng: config.center.lng },
                zoom: config.zoom,
                styles: config.styles,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                zoomControl: true,
            });

            this.markers = {};

            const allItems = [].concat( config.gps || [], config.perks || [] );

            for ( let i = 0; i < allItems.length; i++ ) {
                const item = allItems[i];
                const marker = new window.DimMarker(
                    new google.maps.LatLng( item.lat, item.lng ),
                    this.map,
                    item.type,
                    item.num,
                    this.uid
                );
                this.markers[ item.type + '-' + item.num ] = marker;
            }

            this.bindListHover();

            const self = this;
            google.maps.event.addListener( this.map, 'idle', function() {
                for ( const key in self.markers ) {
                    if ( self.markers.hasOwnProperty( key ) ) {
                        self.markers[key].draw();
                    }
                }
            });
        };

        window.DimMap.prototype.bindListHover = function() {
            const self = this;
            const items = document.querySelectorAll( '.dim-list-item[data-map-uid="' + this.uid + '"]' );

            for ( let i = 0; i < items.length; i++ ) {
                const item = items[i];

                item.addEventListener( 'mouseenter', function() {
                    const key = item.getAttribute( 'data-type' ) + '-' + item.getAttribute( 'data-index' );
                    if ( self.markers[key] ) {
                        self.markers[key].highlight( true );
                    }
                });

                item.addEventListener( 'mouseleave', function() {
                    const key = item.getAttribute( 'data-type' ) + '-' + item.getAttribute( 'data-index' );
                    if ( self.markers[key] ) {
                        self.markers[key].highlight( false );
                    }
                });
            }
        };

        window.DimMarker = function( position, map, type, number, uid ) {
            google.maps.OverlayView.call( this );
            this.position = position;
            this.type = type;
            this.number = number;
            this.uid = uid;
            this.div = null;
            this.setMap( map );
        };

        window.DimMarker.prototype = Object.create( google.maps.OverlayView.prototype );
        window.DimMarker.prototype.constructor = window.DimMarker;

        window.DimMarker.prototype.onAdd = function() {
            const div = document.createElement( 'div' );
            div.className = 'dim-marker dim-marker-' + this.type;
            div.setAttribute( 'data-type', this.type );
            div.setAttribute( 'data-index', this.number );
            div.setAttribute( 'data-map-uid', this.uid );
            div.textContent = this.number;

            if ( 'perk' === this.type ) {
                div.style.zIndex = '200';
            } else {
                div.style.zIndex = '100';
            }

            this.div = div;

            const panes = this.getPanes();
            if ( panes && panes.overlayMouseTarget ) {
                panes.overlayMouseTarget.appendChild( div );
            }

            this.bindEvents( div );
        };

        window.DimMarker.prototype.draw = function() {
            if ( ! this.div ) {
                return;
            }

            const overlayProjection = this.getProjection();
            if ( ! overlayProjection ) {
                return;
            }

            const pos = overlayProjection.fromLatLngToDivPixel( this.position );
            if ( pos ) {
                this.div.style.left = pos.x + 'px';
                this.div.style.top = pos.y + 'px';
            }
        };

        window.DimMarker.prototype.onRemove = function() {
            if ( this.div && this.div.parentNode ) {
                this.div.parentNode.removeChild( this.div );
                this.div = null;
            }
        };

        window.DimMarker.prototype.bindEvents = function( div ) {
            const self = this;

            div.addEventListener( 'mouseenter', function() {
                self.highlight( true );
            });

            div.addEventListener( 'mouseleave', function() {
                self.highlight( false );
            });

            div.addEventListener( 'click', function( e ) {
                e.stopPropagation();
                self.openUrl();
            });
        };

        window.DimMarker.prototype.highlight = function( active ) {
            if ( ! this.div ) {
                return;
            }

            if ( active ) {
                this.div.classList.add( 'is-active' );
            } else {
                this.div.classList.remove( 'is-active' );
            }

            const listItem = document.querySelector(
                '.dim-list-item[data-map-uid="' + this.uid + '"][data-type="' + this.type + '"][data-index="' + this.number + '"]'
            );

            if ( listItem ) {
                if ( active ) {
                    listItem.classList.add( 'is-active' );
                } else {
                    listItem.classList.remove( 'is-active' );
                }
            }
        };

        window.DimMarker.prototype.openUrl = function() {
            const listItem = document.querySelector(
                '.dim-list-item[data-map-uid="' + this.uid + '"][data-type="' + this.type + '"][data-index="' + this.number + '"]'
            );

            if ( listItem && listItem.href ) {
                const win = window.open( listItem.href, '_blank' );
                if ( win ) {
                    win.opener = null;
                }
            }
        };
    }
})();
