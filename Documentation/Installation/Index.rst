.. _installation:

============
Installation
============

Install the extension with Composer. Neither this package nor
EXT:records_list_types is published on Packagist, so add both GitHub
repositories as VCS repositories in your TYPO3 project's root
:file:`composer.json`; Composer does not inherit repository definitions from
dependencies.

.. important::

    For GitHub VCS installations, Composer must be able to see stable release
    tags for both repositories. This release requires EXT:records_list_types
    1.3 or later, whose label catalog and partials it builds on.

.. code-block:: bash
    :caption: Composer installation

    composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
    composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
    composer require webconsulting/records-list-examples:^1.5

Set up the extension and clear caches:

.. code-block:: bash
    :caption: Extension setup

    ./vendor/bin/typo3 extension:setup -e records_list_examples
    ./vendor/bin/typo3 cache:flush

After setup, the six view types appear in the **View** dropdown of
:guilabel:`Content > Records`.

.. _installation-requirements:

Requirements
============

This release supports TYPO3 v14 only:

* TYPO3 CMS 14.3 or later within the v14 series
* PHP 8.4 or 8.5
* EXT:records_list_types 1.3 or later

TYPO3 v13 support has been dropped. The package is installed through Composer
only: it ships no :file:`ext_emconf.php` and is not published in the TYPO3
Extension Repository.
