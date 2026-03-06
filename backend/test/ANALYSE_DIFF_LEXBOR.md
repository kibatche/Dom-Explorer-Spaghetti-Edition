# Analyse diff — lexbor PHP 8.4.18 vs lexbor standalone 2.7.0

> Focus : fichiers de construction d'arbre HTML5 (`html/tree/`, `html/tokenizer/`, `html/parser.c`)
> Date : 2026-03-06

---

## Résumé rapide

| Fichier | Status | Impact comportemental |
|---------|--------|-----------------------|
| `tree/insertion_mode/foreign_content.c` | CHANGED | **OUI — divergence confirmée** |
| `tree/insertion_mode/in_body.c` | CHANGED | OUI — `<select>` nested, `<search>` |
| `tree.c` | CHANGED | OUI — `reset_insertion_mode_appropriately`, `insert_foreign_element` API |
| `tree/active_formatting.c` | IDENTICAL | — |
| `tree/open_elements.c` | IDENTICAL | — |
| `tree/template_insertion.c` | IDENTICAL | — |
| `tree/insertion_mode/in_table.c` | IDENTICAL | — |
| `tree/insertion_mode/in_table_body.c` | IDENTICAL | — |
| `tree/insertion_mode/in_row.c` | IDENTICAL | — |
| `tree/insertion_mode/in_cell.c` | IDENTICAL | — |
| `tree/insertion_mode/in_caption.c` | IDENTICAL | — |
| `tree/insertion_mode/in_column_group.c` | IDENTICAL | — |
| `tree/insertion_mode/in_select.c` | IDENTICAL | — |
| `tree/insertion_mode/in_select_in_table.c` | IDENTICAL | — |
| `tree/insertion_mode/in_template.c` | IDENTICAL | — |
| `tree/insertion_mode/text.c` | IDENTICAL | — |
| `tree/insertion_mode/after_body.c` | IDENTICAL | — |
| `tree/insertion_mode/in_head.c` | IDENTICAL | — |
| `tree/insertion_mode/in_head_noscript.c` | IDENTICAL | — |
| `tokenizer/state_rawtext.c` | CHANGED | Non — linkage uniquement |
| `tokenizer/state_rcdata.c` | CHANGED | Non — linkage uniquement |
| `tokenizer/state_script.c` | CHANGED | Non — linkage uniquement |
| `tokenizer.c` | CHANGED | Non — linkage + PHP line-tracking |
| `parser.c` | CHANGED | Non — linkage |
| `tree/insertion_mode/before_html.c` | CHANGED | Non — linkage |
| `tree/insertion_mode/before_head.c` | CHANGED | Non — linkage |
| `tree/insertion_mode/after_head.c` | CHANGED | Non — linkage |

---

## Findings détaillés

---

### DIFF-01 — `</p>` et `</br>` en foreign content manquants

**Fichier :** `tree/insertion_mode/foreign_content.c`
**Patch :** `diff_tree_insertion_mode_foreign_content.patch`
**Type :** Divergence comportementale confirmée

**Ce qui manque dans PHP :**
```c
// ABSENT en PHP — présent en 2.7.0 seulement :
case LXB_TAG_P:
case LXB_TAG_BR:
    return lxb_html_tree_insertion_mode_foreign_content_all(tree, token);
```

**Comportement PHP :** `</p>` et `</br>` en foreign content tombent dans `anything_else_closed`,
qui tente de pop les éléments pour trouver `</script>` ou le tag correspondant. Le résultat : le `</p>` ou `</br>`
est traité comme un end tag inconnu — sans déclencher le pop out of foreign content qui s'ensuit normalement.

**Comportement 2.7.0 :** `</p>` et `</br>` sont routés vers `foreign_content_all`, qui les traite comme
des start tags HTML (comportement spec §13.2.6.5 : closing tag `</br>` → parse error, reprocess as start tag).
Ce reprocessing déclenche la sortie du foreign content.

**Impact :** Divergence MXSS documentée — P-MXSS-012, P-MXSS-027, etc.
Symfony reste namespace-blind sur le résultat, ce qui limite l'exploitabilité directe.
C'est la **seule** divergence notable dans les fichiers foreign content.

---

### DIFF-02 — Reprocessing token après pop-back de foreign content

**Fichier :** `tree/insertion_mode/foreign_content.c`
**Type :** Différence comportementale subtile, probablement sans impact pratique

**PHP :**
```c
if (tree->fragment != NULL) {
    return lxb_html_tree_insertion_mode_foreign_content_anything_else(tree, token);
}
do {
    lxb_html_tree_open_elements_pop(tree);
    node = lxb_html_tree_current_node(tree);
} while (node && !(mathml_integration || html_integration || node->ns == HTML));

return false;  // ← reprocessing implicite via loop caller
```

**2.7.0 :**
```c
// Pas de fragment guard
while (node != NULL && !(mathml || html || ns == HTML)) {
    lxb_html_tree_open_elements_pop(tree);
    node = lxb_html_tree_current_node(tree);
}
return tree->mode(tree, token);  // ← reprocessing explicite
```

**Analyse :** Le `return false` en PHP et le `return tree->mode(tree, token)` en 2.7.0 ont
vraisemblablement le même effet net (reprocessing dans le nouveau mode). L'impact pratique est nul.
La suppression du `fragment guard` est sans effet pour Symfony (toujours en mode document).

---

### DIFF-03 — `<select>` imbriqué dans `in_body`

**Fichier :** `tree/insertion_mode/in_body.c`
**Type :** Divergence comportementale — non exploitable pour Symfony

**PHP :** `<select><select>` insère deux éléments `<select>` distincts dans l'arbre,
puis change le mode d'insertion en `in_select` ou `in_select_in_table`.

**2.7.0 :** Si un `<select>` est déjà "in scope", l'ouverture d'un nouveau `<select>`
pop l'existant et ne crée pas de nouvel élément. Comportement spec-correct.

```c
// AJOUTÉ en 2.7.0 dans lxb_html_tree_insertion_mode_in_body_select() :
is = lxb_html_tree_is_fragment_element(tree, LXB_TAG_SELECT, LXB_NS_HTML);
if (is) { /* parse error, return */ }

node = lxb_html_tree_element_in_scope(tree, LXB_TAG_SELECT, ...);
if (node != NULL) {
    /* pop until select, return — no new element inserted */
}
```

**Impact :** `<select>` n'est pas dans la allowlist Symfony — la divergence est absorbée.
Potentiellement observable dans un contexte où `<select>` est autorisé côté sanitizer permissif.

---

### DIFF-04 — Suppression du bloc `<select>` dans `reset_insertion_mode_appropriately`

**Fichier :** `tree.c`
**Type :** Refactoring structurel lié à DIFF-03

**PHP :** `reset_insertion_mode_appropriately` avait un bloc spécial pour `<select>` qui
marchait les ancêtres pour décider entre `in_select` et `in_select_in_table`.

**2.7.0 :** Ce bloc est supprimé. La logique est maintenant absorbée par `in_body_select` directement.

**Impact :** Cohérent avec DIFF-03. Sans impact pour Symfony.

---

### DIFF-05 — `lxb_html_tree_insert_foreign_element` : nouveau paramètre `only_add_stack`

**Fichier :** `tree.c`
**Type :** Nouvelle API — sans impact comportemental observable pour Symfony

**2.7.0 :** Ajoute un paramètre `bool only_add_stack`. Si `true`, l'élément est poussé
sur la pile des open elements sans être inséré dans le DOM. Usage : fragment parsing context.

**Impact :** Symfony utilise toujours le mode document — `only_add_stack` est toujours `false`.
Aucune divergence observable.

---

### DIFF-06 — Nouveaux éléments : `<search>`, `<selectedcontent>`

**Fichier :** `tree/insertion_mode/in_body.c`
**Type :** Nouveaux éléments HTML — non pertinents pour Symfony

`<search>` : ajouté comme élément block-level dans les handlers `abcdfhlmnopsu_closed`
et dans la liste qui génère les implied end tags.

`<selectedcontent>` : nouvel élément lié à `<option>` — support
`lxb_html_option_maybe_clone_to_selectedcontent()` ajouté dans `</option>` handler.

**Impact :** `<search>` et `<selectedcontent>` sont des éléments récents. Dans PHP's lexbor,
ils sont traités comme des éléments inconnus. Dans 2.7.0, ils ont une sémantique complète.
Potentiellement observable si ces éléments apparaissent dans un input, mais l'impact sur
la sécurité est nul — ni l'un ni l'autre n'est dans la allowlist Symfony.

---

### DIFF-07 — Suppression du tracking de numéro de ligne

**Fichier :** `tree.c`
**Type :** Code PHP-spécifique retiré de 2.7.0 — sans impact sur le parsing

```c
// RETIRÉ dans 2.7.0 :
node->line = token->line;
/* We only expose line number in PHP DOM */
text->line = tree->tkz_ref->token->line;
/* We only expose line number in PHP DOM */
```

**Explication :** PHP avait ajouté le tracking de position ligne dans les noeuds DOM pour
son API PHP. La version standalone 2.7.0 n'a pas besoin de cette information.
Aucun impact sur le parsing.

---

### DIFF-08 — Tokenizer : changements de linkage exclusivement

**Fichiers :** `tokenizer/state_rawtext.c`, `tokenizer/state_rcdata.c`, `tokenizer/state_script.c`,
`tokenizer.c`, `parser.c`, `before_html.c`, `before_head.c`, `after_head.c`

**Type :** Build system — remplacement de `#define LEXBOR_XXX + #include "str_res.h"`
par des déclarations `LXB_EXTERN` explicites.

```c
// AVANT (PHP) :
#define LEXBOR_TOKENIZER_CHARS_MAP
#define LEXBOR_STR_RES_ANSI_REPLACEMENT_CHARACTER
#include "lexbor/core/str_res.h"

// APRÈS (2.7.0) :
#ifndef LEXBOR_DISABLE_INTERNAL_EXTERN
    LXB_EXTERN const lxb_char_t lexbor_str_res_ansi_replacement_character[4];
    LXB_EXTERN const unsigned char lexbor_tokenizer_chars_map[256];
#endif
```

**Impact :** Zéro impact comportemental. Refactoring interne du système de build.
Les tables de caractères sont identiques.

---

## Conclusion

**Divergences comportementales entre PHP 8.4.18 lexbor et lexbor standalone 2.7.0 :**

| # | Divergence | Ce que fait PHP lexbor | Ce que fait lexbor 2.7.0 |
|---|------------|------------------------|--------------------------|
| DIFF-01 | `</p>` et `</br>` en foreign content | Traite comme end tag inconnu — pas de pop out of foreign content | Reprocesse comme start tag HTML — déclenche le pop (spec-correct) |
| DIFF-02 | Pop-and-reprocess après unrecognized token en foreign content | `return false` (reprocessing implicite via caller) | `return tree->mode(tree, token)` (reprocessing explicite) |
| DIFF-03 | `<select>` imbriqué dans `in_body` | Insère un deuxième élément `<select>` dans l'arbre | Pop jusqu'au `<select>` existant — aucun nouvel élément |
| DIFF-04 | `reset_insertion_mode_appropriately` avec `<select>` | Bloc dédié : marche les ancêtres pour choisir `in_select` vs `in_select_in_table` | Logique absorbée dans `in_body_select` directement |
| DIFF-05 | `insert_foreign_element` | Insère toujours dans le DOM et sur la pile | Peut n'ajouter qu'à la pile (`only_add_stack`) — pour fragment parsing |
| DIFF-06 | `<search>`, `<selectedcontent>` | Éléments inconnus — traités comme generic HTML | Éléments connus avec sémantique block-level / option cloning |
| DIFF-07 | Tracking numéro de ligne | Présent (`node->line`, `text->line`) — PHP DOM API | Absent |
| DIFF-08 | Linkage tokenizer | `#define + #include str_res.h` | `LXB_EXTERN` declarations |

**Ce que ça signifie pour le parsing HTML5 de PHP :**

DIFF-01 est la seule divergence qui modifie le comportement du parser sur des inputs courants.
PHP lexbor ne pop pas hors du foreign content sur `</p>` et `</br>` — là où un parser
spec-conforme (lexbor 2.7.0, navigateurs) le fait. Conséquence : un `</p>` en contexte SVG ou
MathML produit un arbre DOM différent selon le parser. C'est un bug de conformité à la spec §13.2.6.5
dans l'ancienne version, corrigé dans 2.7.0.

DIFF-03 est une vraie différence de comportement sur `<select><select>`, mais elle est sans
conséquence pratique pour les parsers HTML5 modernes qui traitent ce pattern de la même façon côté navigateur.

DIFF-02, DIFF-04 sont des refactorings structurels dont l'effet net est identique.

DIFF-05 est une extension d'API pour le parsing en mode fragment — sans effet en mode document.

DIFF-06 ajoute des éléments récents de la spec HTML (Living Standard) que la vieille version ne connaît pas.

**Bilan :** Il n'y a qu'un seul bug de parsing réel dans PHP lexbor par rapport à la spec :
le cas `</p>` / `</br>` en foreign content. Tous les fichiers d'insertion mode critiques
(`active_formatting.c`, `open_elements.c`, `template_insertion.c`, `in_table.c`,
`in_template.c`, et l'ensemble des modes table) sont **identiques** entre les deux versions.
L'algorithme d'adoption (AAA) est identique. Le foster parenting est identique.
