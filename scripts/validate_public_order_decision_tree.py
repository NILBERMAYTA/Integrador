#!/usr/bin/env python3
"""Validates safety invariants for the public-order decision matrix."""

from itertools import product


LEGALITIES = ("por_verificar", "licita", "ilicita")
CONDUCTS = (
    "cooperadora",
    "no_cooperadora",
    "resistencia_fisica",
    "agresion_no_letal",
    "agresion_letal",
)


def classify(legality: str, conduct: str) -> dict[str, object]:
    result = {
        "force": conduct in {"resistencia_fisica", "agresion_no_letal", "agresion_letal"},
        "dialogue": conduct in {"cooperadora", "no_cooperadora"},
        "specialized": conduct in {"agresion_no_letal", "agresion_letal"},
        "medical": conduct in {"resistencia_fisica", "agresion_no_letal", "agresion_letal"},
        "individualized": conduct == "agresion_letal",
    }

    if legality == "ilicita" and conduct == "cooperadora":
        result["dialogue"] = True
        result["force"] = False

    return result


def main() -> None:
    scenarios = [classify(legality, conduct) for legality, conduct in product(LEGALITIES, CONDUCTS)]

    assert all(not classify(legality, "cooperadora")["force"] for legality in LEGALITIES)
    assert classify("ilicita", "cooperadora")["dialogue"]
    assert all(
        scenario["medical"]
        for scenario in scenarios
        if scenario["force"]
    )
    assert all(
        classify(legality, "agresion_letal")["individualized"]
        for legality in LEGALITIES
    )

    print(f"Validated {len(scenarios)} decision paths and 4 safety invariants.")


if __name__ == "__main__":
    main()
