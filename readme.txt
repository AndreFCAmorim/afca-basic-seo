=== AFCA Basic SEO ===
Contributors: afca
Tags: seo, meta tags, open graph, twitter card, sitemap
Requires at least: 5.7
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Alternativa leve ao Yoast SEO. Só o essencial, sem bloat.

== Description ==

AFCA Basic SEO é um plugin minimalista focado no essencial de SEO para WordPress:

* **Meta title, description e keywords** — por post, página, CPT e termo de taxonomia
* **Open Graph & Twitter Cards** — OG title, description, image por conteúdo (fallback automático para imagem destacada → OG padrão)
* **URL canónica** personalizável
* **Controlo de robots** — noindex / nofollow por conteúdo e por termo
* **Sitemap nativo do WordPress** — escolhe que post types, taxonomias e se queres autores no `wp-sitemap.xml`
* **Exclusão automática** de conteúdo marcado como noindex do sitemap
* **Breadcrumbs via shortcode** `[afca_breadcrumbs]` — com separador personalizável e microdata schema.org (BreadcrumbList)
* **Contador de caracteres** em tempo real no editor

Nada de dashboards, redirecionamentos, ou mil e uma funcionalidades que 99% dos sites não usam.

== Installation ==

1. Carregar a pasta `afca-basic-seo` para `/wp-content/plugins/`
2. Ativar em **Plugins**
3. Ir a **AFCA SEO** no menu lateral para configurar
4. Editar posts/termos para definir meta tags específicas

== Frequently Asked Questions ==

= Funciona com Custom Post Types? =

Sim. Todos os CPTs públicos ganham automaticamente a meta box e aparecem como opção no sitemap.

= Funciona com taxonomias personalizadas? =

Sim — todas as taxonomias públicas.

= É compatível com o Yoast? =

Desativa o Yoast antes. Os dois vão competir pelas mesmas meta tags.

== Changelog ==

= 1.1.0 =
* Novo: shortcode `[afca_breadcrumbs]` com microdata schema.org (BreadcrumbList).
* Novo: definição de separador de breadcrumbs no separador "Geral".
* Suporta atributos no shortcode: `separator`, `home_label`, `show_home`, `show_current`.
* Filtros: `afca_seo_breadcrumb_items` e `afca_seo_breadcrumbs_html`.

= 1.0.0 =
* Lançamento inicial.
