  </main>
</div>
</div>
<script>
// Confirm delete
document.querySelectorAll('form.del-form').forEach(f => {
  f.addEventListener('submit', e => {
    if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) e.preventDefault();
  });
});

// Language tabs (for content forms)
document.querySelectorAll('.lang-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const lang = tab.dataset.lang;
    const parent = tab.closest('form') || tab.closest('.card') || document;
    parent.querySelectorAll('.lang-tab').forEach(t => t.classList.toggle('active', t.dataset.lang === lang));
    parent.querySelectorAll('.lang-panel').forEach(p => p.classList.toggle('active', p.dataset.langPanel === lang));
  });
});
</script>
</body>
</html>
