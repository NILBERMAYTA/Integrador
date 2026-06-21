<?php

namespace App\Services\Chatbot;

enum ChatbotIntent: string
{
    case ArticleAvailability = 'article_availability';
    case ArticlesInMaintenance = 'articles_in_maintenance';
    case ActiveLoans = 'active_loans';
    case ReturnsWithIncidents = 'returns_with_incidents';
    case MostBorrowed = 'most_borrowed';
    case MaintenanceDue = 'maintenance_due';
    case InventorySummary = 'inventory_summary';
    case LowStock = 'low_stock';
    case Incidents = 'incidents';
    case OperationSummary = 'operation_summary';
    case Units = 'units';
    case Help = 'help';
    case Unknown = 'unknown';
}
