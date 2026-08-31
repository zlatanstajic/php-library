SPHINXBUILD ?= sphinx-build
SOURCEDIR = .
BUILDDIR = _build

.PHONY: clean html

clean:
	$(SPHINXBUILD) -M clean "$(SOURCEDIR)" "$(BUILDDIR)"

html:
	$(SPHINXBUILD) --fail-on-warning -M html "$(SOURCEDIR)" "$(BUILDDIR)"
