(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyCHi-3vJT0gaJFuNcRjN2d-QJr8x7UnsP0",
    v: "weekly",
});

async function init() {
    // Import the needed libraries.
    await google.maps.importLibrary('maps');
    await google.maps.importLibrary('marker');

    // Access the map.
    const mapElement = document.querySelector('#karyamap');
    const innerMap = new google.maps.Map(mapElement, {
        center: {
            lat: -6.873957414030999,
            lng: 107.60607448650714,
        },
        zoom: 16,
        mapId: 'f97ce4f54c8a46b674ea6e3b',
    });

    populateMarkers(innerMap);
}

async function fetchData() {
    try {
        const response = await fetch('/wp-json/karyamaps/v1/data');
        if(response.ok) {
            return response.json();
        } else {
            alert('Unable to load map data due to non-ok response');
        }
    } catch(err) {
        alert('Unable to load map data due to network failure');
    }
}

async function populateMarkers(innerMap) {
    const data = await fetchData();
    const bounds = new google.maps.LatLngBounds();

    const markers = [];
    for (const loc of data.data) {
        if(!loc.Coordinate) {
            continue;
        }

        const [lat, lng] = loc.Coordinate.split(',').map(item => parseFloat(item.trim()));

        const label = document.createElement('div');
        label.textContent = loc.Nama;

        const heading = document.createElement('div');
        const title = document.createElement('h2');
        title.textContent = loc.Nama;
        const subtitle = document.createElement('p');
        subtitle.textContent = loc.Subkategori;
        heading.appendChild(title);
        heading.appendChild(subtitle);

        const hyperlink = document.createElement('a');
        hyperlink.href = loc.Link;
        if (loc.image_url !== null) {
            const img = document.createElement('img');
            img.src = loc.image_url;
            img.alt = 'Photo of ' + loc.Nama
            hyperlink.appendChild(img);
        }
        const readMore = document.createElement('div');
        readMore.innerHTML = 'Baca selengkapnya &raquo;';
        hyperlink.appendChild(readMore);
        const content = document.createElement('div');
        content.appendChild(hyperlink);

        const infoWindow = new google.maps.InfoWindow({
            headerContent: heading,
            content,
            ariaLabel: loc.Nama,
            maxWidth: 300,
        });

        let glyph;
        if (loc.image_url === null) {
            glyph = new google.maps.marker.PinElement({
                glyphColor: '#7F7F7F',
                background: '#BFBFBF',
                borderColor: '#7F7F7F',
            });
        } else if (loc.Initials === 'P') {
            glyph = new google.maps.marker.PinElement({
                glyphColor: '#FFFFFF',
                glyphText: 'P',
                background: '#A7779D',
                borderColor: '#FFFFFF',
            });
        } else {
            glyph = new google.maps.marker.PinElement({
                glyphColor: '#FFFFFF',
                glyphText: loc.Initials,
                background: '#8EB962',
                borderColor: '#FFFFFF',
            });
        }
        const marker = new google.maps.marker.AdvancedMarkerElement({
            position: {lat, lng},
            title: loc.Nama,
            gmpClickable: true,
            content: glyph.element
        });
        bounds.extend({lat, lng});
        marker.addEventListener('gmp-click', () => {
            infoWindow.open({
                anchor: marker,
                map: innerMap
            });
        });

        markers.push(marker);
    }
    new markerClusterer.MarkerClusterer({ markers, map: innerMap });
    innerMap.fitBounds(bounds);
}

void init();