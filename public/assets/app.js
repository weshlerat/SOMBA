async function loadCatalog(){const box=document.querySelector('#catalog');try{const r=await fetch('api/products.php');const j=await r.json();if(!j.products?.length){box.innerHTML='<p>Aucun pack actif pour le moment.</p>';return;}box.innerHTML=j.products.map(p=>`<article><div class="icon">🎮</div><h3>${escapeHtml(p.game)}</h3><p>${escapeHtml(p.name)}</p><strong>${Number(p.price).toLocaleString('fr-FR')} ${escapeHtml(p.currency)}</strong><br><button class="btn small" data-id="${p.id}">Acheter</button></article>`).join('');box.querySelectorAll('button').forEach(b=>b.onclick=()=>startOrder(Number(b.dataset.id)));}catch(e){box.innerHTML='<p>Impossible de charger le catalogue.</p>';}}
async function startOrder(id){
 const email=prompt('Ton email :'); if(!email)return;
 const firstName=prompt('Prénom :'); if(!firstName)return;
 const lastName=prompt('Nom :'); if(!lastName)return;
 const phone=prompt('Téléphone (facultatif) :')||'';
 const identifier=prompt('Identifiant joueur / informations de livraison :'); if(!identifier)return;
 const r=await fetch('api/order.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:id,email,first_name:firstName,last_name:lastName,phone,customer:{identifier}})});
 const j=await r.json();
 if(!r.ok){alert(j.error||'Erreur lors de la création du paiement.');return;}
 if(j.redirect_url){window.location.href=j.redirect_url;return;}
 alert('Impossible de démarrer le paiement.');
}
function escapeHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
loadCatalog();
