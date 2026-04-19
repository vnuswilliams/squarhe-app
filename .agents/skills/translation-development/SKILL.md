---
name: translation-development
description: "All strings displayed to the user must be translated usinng the project's localisation systeme (en/fr  by default). No  hardcoded text is allowed in the codebase (controllers, services,  views, components, etc.)."
license: MIT
metadata:
  author: laravel
---

# 🧠 Agent Skill — Laravel Translation Rules

## 🎯 Objectif

L’agent doit implémenter un système de traduction dans Laravel en respectant strictement les règles suivantes :

- Traduire uniquement le texte affiché à l’utilisateur (UI statique)
- Ne jamais traduire les données issues de la base de données
- Support de deux langues uniquement : `en` et `fr`
- Chaque vue ou composant doit avoir son propre fichier de traduction

---

## 🚫 Règle critique — Données dynamiques

L’agent NE DOIT JAMAIS traduire :

- Les variables Blade :
  ```blade
  {{ $user->name }}
  {{ $post->title }}
  {{ $data }}