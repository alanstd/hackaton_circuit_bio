function initMap() {
  const map = L.map('map').setView([48.8566, 2.3522], 13);

  // Géoportail (IGN WMTS)
  const geoportail = L.tileLayer(
    "https://wxs.ign.fr/essentiels/geoportail/wmts?" +
    "SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2" +
    "&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&FORMAT=image/png",
    {
      attribution: "© IGN - Géoportail"
    }
  );

  geoportail.addTo(map);

  L.marker([48.8566, 2.3522])
    .addTo(map)
    .bindPopup("Paris 📍")
    .openPopup();
}
