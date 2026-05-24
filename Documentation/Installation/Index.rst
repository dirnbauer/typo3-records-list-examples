.. _installation:

============
Installation
============

Install the extension with Composer. If Composer cannot find these
packages on Packagist, add both GitHub repositories as VCS repositories
in your TYPO3 project's root :file:`composer.json`; Composer does not
inherit repository definitions from dependencies.

.. important::

    For GitHub VCS installations, Composer must be able to see stable
    release tags for both repositories. This package requires
    EXT:records_list_types 1.0 or later.

.. code-block:: bash
    :caption: Composer installation

    composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
    composer config repositories.records-list-examples vcs https://github.com/dirnbauer/typo3-records-list-examples.git
    composer require webconsulting/records-list-examples:^1.0

Set up the extension and clear caches:

.. code-block:: bash
    :caption: Extension setup

    ./vendor/bin/typo3 extension:setup -e records_list_examples
    ./vendor/bin/typo3 cache:flush

.. _installation-requirements:

Requirements
============

This release supports TYPO3 v14 only:

* TYPO3 CMS 14.3 or later
* PHP 8.3 through 8.5
* EXT:records_list_types 1.0 or later

TYPO3 v13 support has been dropped.
